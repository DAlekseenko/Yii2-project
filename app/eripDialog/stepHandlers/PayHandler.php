<?php

namespace eripDialog\stepHandlers;

use eripDialog\EdRequest;
use eripDialog\EdHelper as H;
use common\models\Services;
use common\components\services\Helper;
use yii\web\NotFoundHttpException;
use api\components\services\Subscription\SubscriberHandler;

class PayHandler extends AbstractEripCaller
{
	public function prepareEripRequest(EdRequest $request)
	{
		$get = parent::prepareEripRequest($request);

		$service = Services::find()->byId($request->getServiceCode())->one();
		$percentCommission = $service->getClientFeeValue() ?: 0;
		$get[H::F_COMMISSION] = $this->calculateCommission($percentCommission, $request->getSum());

		return $get;
	}

	public function beforeAction()
	{
		if (empty($this->transaction)) {
			throw new NotFoundHttpException('Transaction was not found');
		}
		return true;
	}

	public function afterEripCall()
	{
		if (parent::afterEripCall() === false) {
			return false;
		}

		$request = $this->request;
		$response = $this->response;

		$sum = $request->getSum();

		$commissionPercent = $this->transaction->service->getClientFeeValue() ?: 0;
		$commission = $this->calculateCommission($commissionPercent, $sum);

		$this->transaction->updateNewTransaction($sum, $commission, $response->getDateCreate(), $this->response->getPaymentInfo(), $this->cache->getFields());
		if ($this->transaction->save() === false) {
			$response->setError('Возникла внутренняя ошибка сервера');
			return false;
		}

		$response->setFields($this->getCommissionFields($this->transaction->getCommission(), $this->transaction->sum, $this->transaction->getCurrency()));

		return true;
	}

	public function afterAction()
	{
		if (!isset($this->user)) {
			$this->response->addField(H::$phoneField);
		} else {
			$this->setUserSubscriptionInfo($this->user);
		}
	}

	protected function getNextMode()
	{
		return isset($this->user) ? H::MODE_CONFIRM : H::MODE_AUTH;
	}

	protected function calculateCommission($percentCommission, $sum)
	{
		return ceil($sum * ($percentCommission/100));
	}

	protected function getCommissionFields($commission, $sumWithCommission, $currency)
	{
		return [
			['name' => 'Комиссия', 'editable' => false, 'type' => 'R', 'value' => Helper::sumFormat($commission) . ' ' . $currency],
			['name' => 'Cумма с учетом комиссии', 'editable' => false, 'type' => 'R', 'value' => Helper::sumFormat($sumWithCommission) . ' ' . $currency],
		];
	}
}
