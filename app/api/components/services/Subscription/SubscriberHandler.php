<?php

namespace api\components\services\Subscription;

use api\components\services\ParamsService\ParamsService;
use common\models\UssdCharitySubs;
use PbrLibBelCommon\Protocol\RuniverseSubs\RuniverseClient;
use PbrLibBelCommon\Protocol\RuniverseSubs\Message\SubscribeEnd;
use Yii;
use common\models\Users;
use common\components\services\Dictionary;

class SubscriberHandler
{
	/** @var Users */
	protected $user;

	/** @var  SubscriptionClient */
	protected $subscriptionClient;

	public static function createByUser(Users $user)
	{
		return new self($user);
	}

	/**
	 * @param  $uuid
	 * @return SubscriberHandler
	 * @throws \Exception
	 */
	public static function createByUserUuid($uuid)
	{
		$user = Users::find()->byUuid($uuid)->oneForUpdate();
		if (empty($user)) {
			throw new \Exception('Cant get user');
		}
		return new self($user);
	}

	/**
	 * @param $id
	 * @return SubscriberHandler
	 * @throws \Exception
	 */
	public static function createById($id)
	{
		$user = Users::find()->byId($id)->oneForUpdate();
		if (empty($user)) {
			throw new \Exception('Cant get user');
		}
		return new self($user);
	}

	public static function createByMsisdn($msisdn)
	{
		$user = Users::find()->byPhone($msisdn)->oneForUpdate();
		if (empty($user)) {
			$user = new Users();
			$user->phone = $msisdn;
		}
		return new self($user);
	}

	protected function __construct(Users $user)
	{
		$this->user = $user;
		$this->subscriptionClient = $this->getSubscriptionClient();
	}

	/**
	 * @return Users
	 */
	public function getUser()
	{
		return $this->user;
	}

	protected function getSubscriptionClient()
	{
		return \yii::$app->{SERVICE_SUBSCRIPTION_CLIENT};
	}

	/**
	 * @return ParamsService
	 */
	protected function getParamsService()
	{
		return \yii::$app->{SERVICE_PARAMS};
	}

	/**
	 * @param bool $newSubscriberUuid
	 * @param bool $disableBan
	 * @throws \Exception
	 */
	public function clearContract($newSubscriberUuid = false, $disableBan = true)
	{
		$user = $this->user;

		$authManager = Yii::$app->authManager;
		$role = $authManager->getRole(Users::ROLE_BANNED);

		$user->first_name = $user->last_name = $user->password = $user->location_id = $user->email = $user->patronymic = null;
		$user->contract_id_date_change = date('Y-m-d H:i:s', time());
		$user->subscription_status = Users::USER_TYPE_BLANK;
		if ($newSubscriberUuid !== false) {
			$user->subscriber_uuid = $newSubscriberUuid;
		}
		if ($user->save() === false) {
			throw new \Exception("Cant clear user contract");
		}
		if ($disableBan) {
			$user->clearUserResource();
		}
		// стопим подписки по благотворительности
		/** @var RuniverseClient $runiverseClient */
		$runiverseClient = \yii::$app->{SERVICE_RUNIVERSE_CLIENT};
		$charitySubs = UssdCharitySubs::findActiveUserSubscriptions($user->phone);
		/** @var UssdCharitySubs $sub */
		foreach ($charitySubs ?: [] as $sub) {
			$message = new SubscribeEnd($sub->msisdn, $sub->uuid);
			$runiverseClient->callEnd($message);
		}

		$authManager->revoke($role, $user->user_id);
	}

	public function onSubscriptionStart()
	{
		$this->user->subscription_status = Users::USER_TYPE_SUBSCRIBER;
		return $this->user->save();
	}

	public function onSubscriptionEnd()
	{
		if ($this->user->isSubscriber()) {
			$this->user->subscription_status = Users::USER_TYPE_USER;
			return $this->user->save();
		}
		return true;
	}

	public function isUserNew()
	{
		return $this->user->isNewRecord;
	}

	public function saveNewSubscriberUuid($uuid)
	{
		$this->user->subscription_status = Users::USER_TYPE_BLANK;
		$this->user->subscriber_uuid = $uuid;
		$this->user->contract_id_date_change = date('Y-m-d H:i:s', time());

		return $this->user->save();
	}

	/**
	 * @return Entities\UserSubscriptionInfo
	 */
	public function getUserSubscriptionInfo()
	{
		try {
			$history = $this->subscriptionClient->getSubscriptionHistory($this->user->subscriber_uuid, SUBSCRIPTION_PROVIDER_NAME);
			$text = $history->wasSubscribedTo(SUBSCRIPTION_SERVICE_NAME) ?
				Dictionary::subscriptionServiceInfo() :
				Dictionary::subscriptionServiceInfoTrial();

			$agreementRequired = $this->isSubscriptionRequired();
			$info = new Entities\UserSubscriptionInfo($this->user->subscriber_uuid, $agreementRequired, $this->user->subscription_status, $text);

			return $info;
		} catch (\Exception $e) {
			return null;
		}
	}

	public function unsubscribe()
	{
		if ($this->user->isSubscriber()) {
			$this->subscriptionClient->unsubscribeFromService($this->user->subscriber_uuid, SUBSCRIPTION_SERVICE_NAME);
			$this->user->subscription_status = Users::USER_TYPE_UNSUBSCRIBES;

			$this->user->save();
		}
		return true;
	}

	public function changeUserMsisdn($old, $new)
	{
		if ($this->user->phone == $old) {
			$this->user->phone = $new;
			return $this->user->save();
		}
		return true;
	}

	public function isSubscriptionRequired()
	{
		return $this->user->subscription_status <= Users::USER_TYPE_USER
			&& $this->isSubscriptionEnable();
	}

	public function isSubscriptionEnable()
	{
		$params = $this->getParamsService();

		return $params->isSubsModeOn() || $params->isSubsTestPhone($this->user->phone);
	}
}
