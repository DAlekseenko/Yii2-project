<?php

namespace api\components\services\Subscription;

use common\components\services\Dictionary;
use common\components\services\PhoneService;

class SubscriptionClient extends \PbrLibBelCommon\Protocol\Subscription\Service\SubscriptionClient
{
	/**
	 * @param $msisdn
	 * @return \PbrLibBelCommon\Protocol\Subscription\Service\DTO\SubscriptionState
	 * @throws \PbrLibBelCommon\Exceptions\SubscriptionProtocolException
	 * @throws \PbrLibBelCommon\Exceptions\SubscriptionLogicalException
	 * @throws \PbrLibBelCommon\Exceptions\CallerHttpException
	 */
	public function getPotentialSubscriberInfo($msisdn)
	{
		return $this->getActiveSubscriberInfo($msisdn, SUBSCRIPTION_SERVICE_NAME);
	}

	/**
	 * @param $subscriberUuid
	 * @return \PbrLibBelCommon\Protocol\Subscription\Service\DTO\SubscriptionState
	 * @throws \PbrLibBelCommon\Exceptions\CallerHttpException
	 * @throws \PbrLibBelCommon\Exceptions\SubscriptionLogicalException
	 * @throws \PbrLibBelCommon\Exceptions\SubscriptionProtocolException
	 */
	public function getCurrentSubscriberInfo($subscriberUuid)
	{
		return $this->getSubscriberInfo($subscriberUuid, SUBSCRIPTION_SERVICE_NAME);
	}

	/**
	 * Выполняется на событие завершение контракта.
	 *
	 * @param string $subscriberId
	 * @throws \Exception
	 * @throws \yii\db\Exception
	 */
	public function handleContractTermination($subscriberId)
	{
		$transaction = \Yii::$app->getDb()->beginTransaction();
		try {
			$handler = SubscriberHandler::createByUserUuid($subscriberId);
			$handler->clearContract(null);

			$transaction->commit();
		} catch (\Exception $e) {
			$transaction->rollBack();
			throw $e;
		}
	}

	public function handleBlockingStateChange($subscriberId, $isBlocked)
	{
		// TODO: Implement handleBlockingStateChange() method.
	}

	/**
	 * Выполняется на смену номера пользователем
	 *
	 * @param string $subscriberId
	 * @param string $oldMsisdn
	 * @param string $newMsisdn
	 * @throws \Exception
	 * @throws \yii\db\Exception
	 */
	public function handleMsisdnChange($subscriberId, $oldMsisdn, $newMsisdn)
	{
		$transaction = \Yii::$app->getDb()->beginTransaction();
		try {

			$handler = SubscriberHandler::createByUserUuid($subscriberId);
			if ($handler->changeUserMsisdn($oldMsisdn, $newMsisdn) === false) {
				throw new \Exception('Cant save user on MsisdnChange event');
			}
			PhoneService::sendAbstractSms($handler->getUser()->phone, Dictionary::changePhoneSms());

			$transaction->commit();
		} catch (\Exception $e) {
			$transaction->rollBack();
			throw $e;
		}
	}

	/**
	 * Вызывается на событие информирования платформы о новых подписчиках.
	 *
	 * Новый абонет может быть инициализирован самой системой МТС Деньги, тогда существующий пользователь апдейтится с
	 * получением uuid подписчика.
	 *
	 * Новый абонент может быть инициализирован на стороне МТС, тогда создается новый абонент а сервисе МТС Деньги.
	 *
	 * @param string $subscriberId
	 * @param string $msisdn
	 * @throws \Exception
	 * @throws \yii\db\Exception
	 */
	public function handleNewContract($subscriberId, $msisdn)
	{
		$transaction = \Yii::$app->getDb()->beginTransaction();
		try {
			$handler = SubscriberHandler::createByMsisdn($msisdn);
			if ($handler->isUserNew()) {
				PhoneService::sendAbstractSms($handler->getUser()->phone, Dictionary::newUserSubscriptionSms());
			}
			if ($handler->saveNewSubscriberUuid($subscriberId) === false) {
				throw new \Exception("Cant save user on NewContract event($subscriberId)");
			}
			$transaction->commit();
		} catch (\Exception $e) {
			$transaction->rollBack();
			throw $e;
		}
	}

	/**
	 * Выполняется на активацию/дезактивацию подписки
	 *
	 * @param string $subscriberId
	 * @param string $serviceId
	 * @param bool $isSubscribed
	 * @throws \Exception
	 * @throws \yii\db\Exception
	 */
	public function handleSubscriptionStateChange($subscriberId, $serviceId, $isSubscribed)
	{
		$transaction = \Yii::$app->getDb()->beginTransaction();
		try {
			$handler = SubscriberHandler::createByUserUuid($subscriberId);
			if ($isSubscribed) {
				if ($handler->onSubscriptionStart() === false) {
					throw new \Exception('Cant save user on SubscriptionStateChange event');
				}
				$sms = Dictionary::startSubscriptionSms();
			} else {
				if ($handler->onSubscriptionEnd() === false) {
					throw new \Exception('Cant save user on SubscriptionStateChange event');
				}
				$sms = Dictionary::stopSubscriptionSms();
			}
			$transaction->commit();

			PhoneService::sendAbstractSms($handler->getUser()->phone, $sms);
		} catch (\Exception $e) {
			$transaction->rollBack();
			throw $e;
		}
	}
}
