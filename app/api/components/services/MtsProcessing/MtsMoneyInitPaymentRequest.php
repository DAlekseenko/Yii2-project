<?php
/**
 * Created by PhpStorm.
 * User: kirsanov
 * Date: 20.06.2017
 * Time: 11:17
 */

namespace api\components\services\MtsProcessing;

use PbrLibBelCommon\Protocol\McProcessing\AdditionalParams;
use PbrLibBelCommon\Protocol\McProcessing\InitPaymentRequest;
use yii\helpers\ArrayHelper;

class MtsMoneyInitPaymentRequest extends InitPaymentRequest
{
	public static function createFromArray(array $params)
	{
		$instance = new self();

		$additionalParams = AdditionalParams::newFromArray(ArrayHelper::getValue($params, 'additionalParams', []));
		$instance
			->setServiceName(ArrayHelper::getValue($params, 'serviceName'))
			->setTextProfileId(ArrayHelper::getValue($params, 'textProfileId'))
			->setAdditionalParams($additionalParams);

		return $instance;
	}

	public function setTransactionData($msisdn, $paymentUuid, $sum, $subscriberUuid)
	{
		$this->setAccountID($subscriberUuid);
		$this->setMsisdn($msisdn);
		$this->setSumForAbonent($sum);
		$this->setSumForProcessing($sum);
		$this->setPaymentUUID($paymentUuid);

		return $this;
	}

	public function setSubscriptionService($service)
	{
		$this->setServiceNumber($service);

		return $this;
	}
}
