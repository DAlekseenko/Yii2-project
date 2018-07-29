<?php

namespace common\components\services;

use PbrLibBelCommon\Caller\Caller;
use PbrLibBelCommon\Protocol\Utility\MtsbelUtilityClient;
use Yii;
use common\models\PaymentTransactions;

class PhoneService
{
	public static function sendPassword($phone, $password)
	{
		return self::sendSms(Yii::$app->params['sendPasswordSource'], ['phone' => $phone, 'password' => $password]);
	}

	public static function sendPayContinueCode($phone, $code)
	{
		return self::sendSms(Yii::$app->params['sendPayContinueCode'], ['phone' => $phone, 'code' => $code]);
	}

	public static function sendWelcomeMessage($phone)
	{
		return self::sendSms(Yii::$app->params['sendWelcomeMessage'], ['phone' => $phone]);
	}

	public static function sendSimpleSms($phone, $text)
	{
		return self::sendSms(Yii::$app->params['sendWelcomeMessage'], ['phone' => $phone, 'text' => $text]);
	}

	/**
	 * Отправляет сообщение об успехе, согласна настройкам в транзакции
	 *
	 * @param PaymentTransactions $transaction
	 * @return bool|null
	 */
	public static function sendPaymentSuccess(PaymentTransactions $transaction)
	{
		$customSms = $transaction->getCustomSuccessMsg();
		if (isset($customSms)) {
			return self::sendSms(
			    Yii::$app->params['sendPaymentCustomSuccess'],
                [
                    'phone' => $transaction->user->phone,
                    'msg' => $customSms['text'],
                    'product' => isset($customSms['product']) ? $customSms['product'] : MC_SEND_SMS_PRODUCT
                ]
            );
		}
		return $transaction->canSendSuccessSms() ? self::sendSms(Yii::$app->params['sendPaymentSuccess'], ['phone' => $transaction->user->phone, 'service' => $transaction->service->name, 'totalSum' => $transaction->sum, 'currency' => $transaction->getCurrency()]) : null;
	}

	public static function sendAbstractSms($phone, $text)
	{
		return self::sendSms(Yii::$app->params['sendSms'], ['phone' => $phone, 'text' => $text]);
	}

	private static function sendSms($params, array $replace)
	{
	    $caller = new Caller($params['url']);
	    $caller->bulkSetGetParameters(
	        TemplateHelper::fillTemplates($params['get'],$replace)
        );

	    try {
            $caller->call();
            return true;
        } catch (\Exception $e) {
	        return false;
        }
	}

	static function isBelorussPhone($phone)
	{
		if (YII_DEBUG === true) {
			return true;
		}

		try {
			$logger = Yii::$app->{LOG_CATEGORY_MTS_UTILITY};
			$mtsClient = new MtsbelUtilityClient($logger, MTS_API_URL);

            return $mtsClient->isMsisdnValid($phone);
        } catch (\Exception $e) {
            return false;
        }
	}

	static function getBalance($phone)
	{
	    try {
			$logger = Yii::$app->{LOG_CATEGORY_MTS_UTILITY};
			$mtsClient = new MtsbelUtilityClient($logger, MTS_API_URL);

            return $mtsClient->getSubscriberBalance($phone);
        } catch (\Exception $e) {
            return false;
        }
	}
}