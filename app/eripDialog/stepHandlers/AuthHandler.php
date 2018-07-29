<?php

namespace eripDialog\stepHandlers;

use api\components\services\Subscription\RegistrationService;
use common\models\virtual\ApiRegistration;
use api\models\exceptions\ModelException;
use eripDialog\EdHelper as H;
use api\components\services\Subscription\SubscriberHandler;

class AuthHandler extends AbstractHandler
{
	public function beforeAction()
	{
		$model = new ApiRegistration();
		$registrationService = new RegistrationService($model);
		if ($registrationService->validate(\Yii::$app->request->get()) === false) {
			throw new ModelException($model);
		}
		/** @var \common\components\services\Environment $environment */
		$environment = \Yii::$app->environment;
		$user = $registrationService->registerUser($environment);
		if ($user === false) {
			throw new ModelException($model);
		}
		if ($registrationService->sendRegistrationCode($user) !== false) {
			$this->cache->appendProperty('phone', $model->phone);
			$this->cache->appendProperty(H::F_MODE, $this->getNextMode());
			$this->setUserSubscriptionInfo($user);
			return true;
		}
		if ($model->hasErrors('phone')) {
			throw new ModelException($model);
		}
		return false;
	}

	public function afterAction()
	{
		$this->response->addField(H::$codeField);
	}

	public function getNextMode()
	{
		return H::MODE_CONFIRM;
	}
}