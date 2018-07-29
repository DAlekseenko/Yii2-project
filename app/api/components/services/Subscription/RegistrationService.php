<?php

namespace api\components\services\Subscription;

use common\components\services\Environment;
use common\models\Users;
use common\models\UserPasswords;
use common\components\services\PhoneService;
use common\models\virtual\Registration;
use PbrLibBelCommon\Exceptions\CallerHttpException;
use PbrLibBelCommon\Exceptions\SubscriptionLogicalException;
use PbrLibBelCommon\Exceptions\SubscriptionProtocolException;
use \yii\base\Model;

class RegistrationService
{
	/** @var  \yii\base\Model|Registration */
	protected $registrationModel;

	public function __construct(Model $model)
	{
		$this->registrationModel = $model;
	}

	public function validate($data)
	{
		if ($this->registrationModel->load($data) === false) {
			$this->registrationModel->addError('phone', 'Техническая ошибка');
			return false;
		}

		return $this->registrationModel->validate();
	}

	/**
	 * @param Environment|null $env
	 * @return bool|Users
	 * @throws \Exception
	 * @throws \Throwable
	 */
	public function registerUser(Environment $env = null)
	{
		/** @var SubscriptionClient $subscriptionClient */
		$subscriptionClient = \yii::$app->{SERVICE_SUBSCRIPTION_CLIENT};
		try {
			$user = $this->registrationModel->getUser($env);
		} catch (\Exception $e) {
			$message = $e->getMessage();
			\yii::error("REGISTRATION: Cant save user:\n$message");
			$this->registrationModel->addError('phone', 'Ошибка регистрации абонента');
			return false;
		}

		try {
			if ($user->subscriber_uuid === null) {
				$subscriptionClient->getPotentialSubscriberInfo($this->registrationModel->phone);
			} else {
				$subscriptionClient->getCurrentSubscriberInfo($user->subscriber_uuid);
			}
		} catch (SubscriptionLogicalException $e) {
			$this->registrationModel->addError('phone', 'Сервис только для абонентов белорусского МТС');
			$this->registrationModel->deleteTempUser($user);
			return false;
		} catch (SubscriptionProtocolException $e) {
			$this->registrationModel->addError('phone', 'Ошибка регистрации абонента');
			$this->registrationModel->deleteTempUser($user);
			return false;
		} catch (CallerHttpException $e) {
			$this->registrationModel->addError('phone', 'Сервер МТС не отвечает, попробуйте позже');
			return false;
		}

		if ($user->isBanned()) {
			$this->registrationModel->addError('phone', 'Номер заблокирован');
			return false;
		}

		return $user;
	}

	public function sendRegistrationCode(Users $user)
	{
		$userPassword = $user->userPassword ?: new UserPasswords();
		$userPassword->generatePassword();
		$userPassword->user_id = $user->getPrimaryKey();
		if (!$userPassword->save(false) || !PhoneService::sendPassword($user->phone, $userPassword->password)) {
			$this->registrationModel->addError('phone', 'Ошибка отправки пароля');
			return false;
		}
		return true;
	}
}
