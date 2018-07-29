<?php
namespace common\components\services;

use PbrLibBelCommon\Caller\Caller;
use yii;

class Helper
{
	const FORMAT_SPAN = '<span class="phone-code">+###</span> (##)-###-####';
	const FORMAT_SIMPLE = '(###) ##-###-####';

	const MD5_SALT = '0Byklxc2-SJxTVzh6cDhXTzhZQnM';

	public static function formatPhone($phone, $format = Helper::FORMAT_SPAN)
	{
		$count = 0;
		return preg_replace_callback('/#/u', function () use ($phone, &$count) {
			return substr($phone, $count++, 1);
		}, $format);
	}

	public static function sumFormat($sum)
	{
		return number_format($sum, 2, '.', ' ');
	}

	public static function prepareSum($sum)
	{
		return number_format($sum, 2, '.', '');
	}

	public static function makeSdpUrl($connectionId, $queue, $deviceId)
	{
	    $caller = new Caller(Yii::$app->params['SdpUrl']['url']);
	    $caller->bulkSetGetParameters(
	        TemplateHelper::fillTemplates(
                Yii::$app->params['SdpUrl']['get'],
                [
                    'connection_id' => $connectionId,
                    'answer_to' => $queue,
                    'device_id' => $deviceId,
                    'key' => md5($connectionId . $queue . $deviceId . self::MD5_SALT),
                ]
            )
        );
	    return $caller->buildUrl();
	}

	public static function checkParamsByKey(array $params, $key)
	{
		return md5(implode('', $params) . self::MD5_SALT) == $key;
	}

	public static function createUuid()
	{
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
					   mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479),
					   mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
	}
}