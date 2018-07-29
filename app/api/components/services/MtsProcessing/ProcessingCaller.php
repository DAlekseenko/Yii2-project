<?php

namespace api\components\services\MtsProcessing;

use yii;
use yii\helpers\ArrayHelper;
use PbrLibBelCommon\Protocol\MQ\MqCaller;

class ProcessingCaller
{
	private $request = null;

	private $requestMessage = null;

	private $processingUrl = null;

	public function __construct($product, $msisdn, $transactionUuid, $sum, $subscriberUuid)
	{
		$params = yii::$app->params;
		if (!isset($params['McProducts'][$product])) {
			throw new \Exception("Params for product $product were not found");
		}
		$productParams = $params['McProducts'][$product];

		$this->request = MtsMoneyInitPaymentRequest::createFromArray($productParams);
		$this->request->setTransactionData($msisdn, $transactionUuid, $sum * 1000000, $subscriberUuid); // $sum - BYR c копейками
		$this->processingUrl = ArrayHelper::getValue($productParams, 'url');
		$this->requestMessage = MtsMoneyMqMessage::create(ArrayHelper::getValue($productParams, 'callbackUrl'));
	}

	public function setAccept($acceptSms = null)
	{
		$additionalParams = $this->request->getAdditionalParams();
		$additionalParams->setParameterByPath('ServiceParams.IsAccept', '1');
		if ($acceptSms) {
			$additionalParams->setParameterByPath('ServiceParams.AcceptParams.SmsTemplate', $acceptSms);
		}
		return $this;
	}

	public function setSubscriptionService($service)
	{
		$this->request->setSubscriptionService($service);

		return $this;
	}

	public function call()
	{
		$this->requestMessage->setMqMessageData($this->request->buildRootElement());
		$caller = new MqCaller($this->processingUrl);
		$result = $caller->callWithPost($this->requestMessage->buildXmlString());
		yii::info("InitPaymentRequest: $result");
	}
}
