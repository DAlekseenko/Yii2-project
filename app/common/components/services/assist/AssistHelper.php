<?php

namespace common\components\services\assist;

class AssistHelper
{
	const ERROR_BAD_REQUEST = 3;
	const ERROR_ENCRYPTION = 9;
	const ERROR_NO_TRANSACTION = 10;
	const ERROR_INTERNAL = 2;

	private static $salt = 'at17oms2';

	/**
	 * @param $assistAnswer
	 * @return int
	 */
	public static function checkData($assistAnswer)
	{
		if (!isset(
			$assistAnswer['merchant_id'],
			$assistAnswer['ordernumber'],
			$assistAnswer['amount'],
			$assistAnswer['currency'],
			$assistAnswer['orderstate'],
			$assistAnswer['checkvalue'],
			$assistAnswer['billnumber'],
			$assistAnswer['packetdate']
		)) {
			return self::ERROR_BAD_REQUEST;
		}

		$x = implode('', [$assistAnswer['merchant_id'], $assistAnswer['ordernumber'], $assistAnswer['amount'], $assistAnswer['currency'], $assistAnswer['orderstate']]);
		$checkSum = strtoupper(md5(strtoupper(md5(self::$salt) . md5($x))));

		return $checkSum == $assistAnswer['checkvalue'] ? 0 : self::ERROR_ENCRYPTION;
	}
}
