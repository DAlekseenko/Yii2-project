<?php
namespace frontend\models\virtual;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use common\models\Invoices;
use common\models\PaymentTransactions;
use common\models\Users;
use common\components\services\Environment;
use eripDialog\EdApplication;
use eripDialog\exceptions\EdCategoryLimitException;
use eripDialog\exceptions\EdDayLimitException;
use eripDialog\exceptions\EdLimitException;
use eripDialog\exceptions\EdMonthLimitException;
use common\components\services\PhoneService;

class InvoicesMobileForm extends Model
{
	public $transactionIds = [];
	public $ids = [];
	public $userId = null;
	private $invoices = null;
	/** @var PaymentTransactions[]|null  */
	private $transactions = null;

	public function validate($attributeNames = null, $clearErrors = true)
	{
		$result = parent::validate($attributeNames, $clearErrors);
		if (isset($this->userId)) {
			$identity = Users::findIdentity($this->userId);
		} else {
			$identity = yii::$app->user->identity;
		}
		return $result && $this->getInvoices() && $identity->canPay($this->getTotalSum(), $text);
	}

	public function init()
	{
		parent::init();
		$this->ids = ArrayHelper::getColumn($this->getInvoices(), 'id');
		$this->transactionIds = ArrayHelper::getColumn($this->getTransactions(), 'id');
	}

	/**
	 * @return array массив из новых transaction_id
	 */
	public function pay()
	{
		$result = [];
		if ($this->validate()) {
			foreach ($this->getInvoices() as $invoice) {
				if ($transactionId = $this->payInvoice($invoice)) {
					$result[] = $transactionId;
				}
				/** @todo ставим таймаут для того, чтобы не отправлять одновременно транзакции */
				usleep(100000);
			}
		}
		return $result;
	}

	public function getSum()
	{
		return array_sum(ArrayHelper::getColumn($this->getInvoices(), 'sum'));
	}

	public function getTotalSum()
	{
		return array_sum(ArrayHelper::getColumn($this->getInvoices(), 'totalSum'));
	}

	public function getInvoices()
	{
		if ($this->invoices === null) {
			if (!empty($this->ids)) {
				$with = ['service.servicesInfo', 'service.location', 'service.category.categoriesInfo', 'transaction'];
				$this->invoices = Invoices::findActive($this->userId)->with($with)->byId($this->ids)->all();
				$this->transactions = ArrayHelper::getColumn($this->invoices, 'transaction');
			} elseif (!empty($this->transactionIds)) {
				$with = ['invoice', 'invoice.service.servicesInfo', 'invoice.service.location', 'invoice.service.category.categoriesInfo'];
				$this->transactions = PaymentTransactions::find()->where(['user_id' => $this->userId])->onlyNew()->with($with)->byId($this->transactionIds)->all();
				$this->invoices = ArrayHelper::getColumn($this->transactions, 'invoice');
			}
		}
		return $this->invoices;
	}

	/**
	 * @return \common\models\PaymentTransactions[]|null
	 */
	public function getTransactions()
	{
		if ($this->transactions === null) {
			$this->getInvoices();
		}
		return $this->transactions;
	}

	public function setEnvironment(Environment $env)
	{
		/** @var PaymentTransactions $transaction */
		foreach ($this->getTransactions() ?: [] as $transaction) {
			$transaction->setOnPayEnvironment($env)->save();
		}
	}

	/**
	 * @param  Invoices $invoice
	 * @return bool|int
	 * @throws \Exception
	 */
	public function payInvoice($invoice)
	{
		/** @var \api\components\services\ParamsService\ParamsService $paramsService */
		$paramsService = \yii::$app->{SERVICE_PARAMS};
		try {
			return Yii::$app->db->transaction(function () use ($invoice, $paramsService) {
				try {
					$response = EdApplication::confirmPayment($invoice->transaction);
				} catch (EdLimitException $e) {
					if ($e instanceof EdMonthLimitException) {
						PhoneService::sendAbstractSms($invoice->user->phone, $paramsService->makeMonthLimitMsg($e->getAvailableSum()) );
					} elseif ($e instanceof EdDayLimitException) {
						PhoneService::sendAbstractSms($invoice->user->phone, $paramsService->makeDayLimitMsg($e->getAvailableSum()) );
					} elseif ($e instanceof EdCategoryLimitException) {
						PhoneService::sendAbstractSms($invoice->user->phone, $paramsService->makeCategoryLimitMsg($e->getAvailableSum(), $e->getCategoryName()) );
					}
					return $invoice->transaction->getPrimaryKey();
				}

				if (!$response->hasErrors()) {
					return $invoice->transaction->getPrimaryKey();
				} else {
					throw new \Exception();
				}
			});
		} catch (\Exception $e) {
			yii::error('pay invoice error:');
			yii::info($invoice->getAttributes());
			yii::info($e);
			return false;
		}
	}
}
