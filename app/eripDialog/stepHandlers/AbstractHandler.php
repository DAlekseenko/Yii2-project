<?php

namespace eripDialog\stepHandlers;

use api\components\services\Subscription\Entities\UserSubscriptionInfo;
use eripDialog\EdApplication;
use eripDialog\EdHelper as H;
use yii\base\UnknownMethodException;
use api\components\services\Subscription\SubscriberHandler;
use common\models\Users;

/**
 * @property \eripDialog\EdRequest $request
 * @property \eripDialog\EdResponse $response
 * @property \eripDialog\EdCache $cache
 * @property \eripDialog\EdLogger $logger
 * @property \common\models\PaymentTransactions $transaction
 * @property \common\models\Users $user
 */
abstract class AbstractHandler implements InterfaceStepHandler
{
	/** @var EdApplication */
	protected $app;

	/** @var  UserSubscriptionInfo */
	protected $subscription = null;

	public function __construct(EdApplication $app)
	{
	 	$this->app = $app;
	}

	public function __get($name)
	{
		$method = 'get' .  ucfirst($name);
		if (method_exists($this->app, $method)) {
			return $this->app->$method();
		}
		throw new UnknownMethodException();
	}

	public function __set($name, $value)
	{
		$method = 'set' .  ucfirst($name);
		if (!method_exists($this->app, $method)) {
			throw new UnknownMethodException();
		}
		$this->app->$method($value);
	}

	public function __isset($name)
	{
		try {
			return $this->$name !== null;
		} catch (\Exception $e) {
			return false;
		}
	}

	public function setUserSubscriptionInfo(Users $user)
	{
		$subscriberHandler = SubscriberHandler::createByUser($user);
		$this->subscription = $subscriberHandler->getUserSubscriptionInfo();
	}

	public function prepareClientOutput()
	{
		$response = $this->response;
		if ($response->hasErrors()) {
			return $response->get();
		}

		$fields = $response->getFields();
		foreach ($fields as &$field) {
			if (!isset($field['originalField'])) {
				$field['originalField'] = true;
				$field['mask'] = null;
				$field['description'] = $field['name'];
				if ($field['editable']) {
					$field['required'] = !$response->isSum();
				}
			}
		}
		$mode = $this->request->getMethodName();

		return [
			H::F_MODE => $this->getNextMode(),
			H::F_FIELDS => $fields,
			H::F_SUMMARY => $mode == 'start' || (isset($this->user) && $mode == 'fields') ? $response->getSummary() : null,
			H::F_MTS_MONEY_SESSION => $this->request->getSession(),
			'subscription' => $this->subscription
		];
	}

	abstract protected function getNextMode();
}
