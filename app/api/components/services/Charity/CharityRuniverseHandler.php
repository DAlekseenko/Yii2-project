<?php
/**
 * Created by PhpStorm.
 * User: kirsanov
 * Date: 31.01.2018
 * Time: 15:23
 */

namespace api\components\services\Charity;

use common\models\UssdCharitySubs;
use PbrLibBelCommon\Protocol\RuniverseSubs\Message\SubscribeInitFail;
use PbrLibBelCommon\Protocol\RuniverseSubs\Message\SubscribePaymentFail;
use PbrLibBelCommon\Protocol\RuniverseSubs\Message\SubscribePaymentSuccess;
use PbrLibBelCommon\Protocol\RuniverseSubs\Message\SubscribeStart;
use PbrLibBelCommon\Protocol\RuniverseSubs\Message\SubscribeStop;
use PbrLibBelCommon\Protocol\RuniverseSubs\RuniverseSubsHandler;
use common\components\services\PhoneService;
use common\components\services\MqJobMessage;

class CharityRuniverseHandler extends RuniverseSubsHandler
{
	/**
	 * Событие на начало подписки
	 *
	 * @param SubscribeStart $message
	 */
	public function handleSubscribeStart(SubscribeStart $message)
	{
		$subs = UssdCharitySubs::findUserSubscription($message->getSubscribeIdAsString(), $message->getMsisdn());
		if (!empty($subs)) {
			$subs->start_date = date('Y-m-d H:i:s', time());
			$subs->save();
			if (isset($subs->ussd)) {
				PhoneService::sendAbstractSms($message->getMsisdn(), $subs->ussd->subscribe_start_sms);
			}
		}
	}

	/**
	 * Событие на невозможность подписки
	 *
	 * @param SubscribeInitFail $message
	 */
	public function handlerSubscribeInitFail(SubscribeInitFail $message)
	{
		$subs = UssdCharitySubs::findUserSubscription($message->getSubscribeIdAsString(), $message->getMsisdn());
		if (!empty($subs)) {
			$subs->start_date = $subs->stop_date = date('Y-m-d H:i:s', time());
			$subs->save();
		}
	}

	/**
	 * Событие на завершение подписки
	 *
	 * @param SubscribeStop $message
	 */
	public function handlerSubscribeStop(SubscribeStop $message)
	{
		$subs = UssdCharitySubs::findUserSubscription($message->getSubscribeIdAsString(), $message->getMsisdn());
		if (!empty($subs)) {
			$subs->stop_date = date('Y-m-d H:i:s', time());
			$subs->save();
			if (isset($subs->ussd)) {
				PhoneService::sendAbstractSms($message->getMsisdn(), $subs->ussd->subscribe_end_sms);
			}
		}
	}

	/**
	 * Событие на ошибку тарификации
	 *
	 * @param SubscribePaymentFail $message
	 */
	public function handleSubscribePaymentFail(SubscribePaymentFail $message)
	{
		// Нет действий на это событие
	}

	/**
	 * Событие тарификации
	 *
	 * @param SubscribePaymentSuccess $message
	 */
	public function handleSubscribePaymentSuccess(SubscribePaymentSuccess $message)
	{
		try {
			$subs = UssdCharitySubs::findUserSubscription($message->getSubscribeIdAsString(), $message->getMsisdn());
			if (!empty($subs) && isset($subs->ussd)) {

				/** @var \common\components\services\MqConnector $connector */
				$connector = \Yii::$app->amqp;
				$connector->sendMessageDirectly(new MqJobMessage(QUEUE_SEQUENTIAL_JOBS, 'handlers/pay-by-phone', [
					$message->getMsisdn(),
					$subs->ussd->plug,
					$subs->ussd->code
				]));
			}
		} catch (\Exception $e) {
			$this->logger->error("Payment Success handler Error: {$e->getMessage()}");
		}
	}
}
