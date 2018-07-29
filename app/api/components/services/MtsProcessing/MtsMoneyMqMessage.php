<?php
/**
 * Created by PhpStorm.
 * User: kirsanov
 * Date: 20.06.2017
 * Time: 11:44
 */

namespace api\components\services\MtsProcessing;

use PbrLibBelCommon\Protocol\MQ\MqMessage;
use common\components\services\Helper;

class MtsMoneyMqMessage extends MqMessage
{
	public static function create($returnAddress)
	{
		return parent::createFromValues(Helper::createUuid(), null, null, null, $returnAddress);
	}
}
