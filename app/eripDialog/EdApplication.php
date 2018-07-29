<?php

namespace eripDialog;

use yii;
use yii\base\Component;
use common\models\PaymentTransactions;
use common\models\Users;
use common\components\services\Helper;
use eripDialog\stepHandlers;
use eripDialog\EdHelper as H;
use Exception;

/**
 * Class EdApplication
 * @package eripDialog
 *
 * @method
 */
final class EdApplication extends Component
{
	/** @var null|$this  */
	private static $app = null;

	/** @var EdRequest */
	private $request;

	/** @var EdResponse */
	private $response;

	/** @var \eripDialog\stepHandlers\AbstractEripCaller */
	private $stepHandler;

	/** @var EdCache */
	private $cache;

	/** @var Users */
	private $user;

	/** @var PaymentTransactions */
	private $transaction;

	/** @var EdLogger */
	private $logger;

	/** @var null|callable */
	private $onTransactionCreate = null;

	public static function getInstance(array $config = [])
	{
		if (self::$app === null) {
			self::$app = new self($config);
		}

		return self::$app;
	}

	public function init()
	{
		parent::init();

		$this->request = $this->request ?: new EdRequest();
		$this->response = $this->response ?: new EdResponse();
		$this->cache = $this->cache ?: new EdCache();
		$this->user = isset(yii::$app->user, yii::$app->user->identity) ? yii::$app->user->identity : null;
	}

	public function getRequest()
	{
		return $this->request;
	}

	public function getResponse()
	{
		return $this->response;
	}

	public function getCache()
	{
		return $this->cache;
	}

	public function getTransaction()
	{
		if (empty($this->transaction)) {
			$uuid = $this->getRequest()->getTransaction();
			if ($uuid !== null) {
				$this->transaction = PaymentTransactions::find()->whereTransaction($uuid)->onlyNew()->one();
			}
		}

		return $this->transaction;
	}

	public function setRequest(EdRequest $request)
	{
		$this->request = $request;
	}

	public function setLogger(EdLogger $logger)
	{
		$this->logger = $logger;
	}

	public function setResponse(EdResponse $response)
	{
		$this->response = $response;
	}

	public function setUser(Users $user)
	{
		$this->user = $user;
	}

	public function getStepHandler()
	{
		return $this->stepHandler;
	}

	public function getLogger()
	{
		return $this->logger;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function setTransaction(PaymentTransactions $transactions)
	{
		$this->transaction = $transactions;
	}

	public function processNewTransaction(PaymentTransactions $tr)
	{
		if (isset($this->onTransactionCreate)) {
			$func = $this->onTransactionCreate;
			$func($tr);
		}
	}

	/**
	 * @param callable $onTransactionCreate
	 */
	public function setOnTransactionCreate(callable $onTransactionCreate)
	{
		$this->onTransactionCreate = $onTransactionCreate;
	}

	/**
	 * @param array $get
	 * @param bool $usingCache
	 * @throws Exception
	 * @throws \eripDialog\exceptions\EdLimitException
	 * @throws \Throwable
	 */
	public function run(array $get = [], $usingCache = true)
	{
		$request = $this->request;

		if (!empty($get)) {
			$request->set($get);
		}
		if ($usingCache) {
			// Если первый вход, то создаем сессию диалога
			$cacheId = $request->isStart() ? Helper::createUuid() : $request->getSession();

			$this->cache->setId($cacheId);

			// Если в запросе пришел идентификатор сессии, то мы должны проверить валидность кеша по этому идентификатору
			if ($request->hasSession() && $this->cache->validate() === false) {
				throw new Exception('Session data was not found');
			}
			$request->mergeWithCache($this->cache);
		}

		$methodName = $request->getMethodName();
		if (!isset($methodName)) {
			throw new Exception('Method is not defined');
		}
		$handler = 'eripDialog\stepHandlers\\' . ucfirst($methodName) . 'Handler';
		if (!class_exists($handler)) {
			throw new Exception('Handler for method "' . $methodName . '" was not found.');
		}
		/** @var $stepHandler \eripDialog\stepHandlers\AbstractEripCaller|\eripDialog\stepHandlers\AbstractHandler */
		$stepHandler = new $handler($this);

		//\Yii::$app->db->transaction(function() use ($stepHandler) {
			if ($stepHandler->beforeAction() === true) {
				if ($stepHandler instanceof stepHandlers\AbstractEripCaller) {
					$stepHandler->beforeEripCall();
					$stepHandler->eripCall();
					$stepHandler->afterEripCall();
				}
				$stepHandler->afterAction();
			}
		///});
		$this->stepHandler = $stepHandler;
	}

	/**
	 * @param PaymentTransactions $transaction
	 * @return EdResponse
	 * @throws Exception
	 * @throws \Throwable
	 * @throws exceptions\EdLimitException
	 */
	public static function confirmPayment(PaymentTransactions $transaction)
	{
		$app = new self();
		if (!isset($transaction->user)) {
			throw new Exception('User is not defined');
		}
		$app->setUser($transaction->user);
		$app->setTransaction($transaction);
		$app->run([H::F_MODE => H::MODE_CONFIRM], false);

		return $app->getResponse();
	}

	/**
	 * @param $serviceId
	 * @param $sum
	 * @param array $values
	 * @return bool
	 * @throws Exception
	 * @throws \Throwable
	 * @throws exceptions\EdLimitException
	 */
	public function backgroundRun($serviceId, $sum, array $values = [])
	{
		$edRequest = new EdRequest();
		$edResponse = $this->getResponse();
		$edRequest->set(['serviceCode' => $serviceId]);

		for ($i = 1; $i <= 15; $i++) {
			$this->setRequest($edRequest);
			$edResponse->clear();
			$this->run();
			if ($edResponse->hasErrors()) {
				return false;
			}
			$nextRequest = [H::F_MTS_MONEY_SESSION => $this->getCache()->getId()];

			if ($edResponse->countEditableFields() > 0) {
				$nextRequest[H::F_FIELDS] = [];
				foreach ($edResponse->getEditableFields() as $key => $value) {
					$nextRequest[H::F_FIELDS][$key] = array_shift($values);
				}
			}
			if ($edResponse->isSum()) {
				$nextRequest[H::F_SUM] = $sum;
			}
			if ($edRequest->getMethodName() == H::MODE_CONFIRM) {
				return true;
			}
			$edRequest = new EdRequest();
			$edRequest->set($nextRequest);
		}
		return false;
	}
}
