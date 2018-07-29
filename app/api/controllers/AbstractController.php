<?php

namespace api\controllers;

use yii;
use api\components\services\ControllerModelErrorTrait;
use common\models\UserDevices;
use common\models\Users;

class AbstractController extends yii\web\Controller
{
	use ControllerModelErrorTrait;

	/** @var array Методы, которые требуют sms-подтверждения */
	public $codeRequiredMethods = [
		'invoices/pay-invoice',
		'erip-dialog/index'
	];

	public function beforeAction($action)
	{
		$this->enableCsrfValidation = false;
		$this->setEnvironment();
		$this->checkCode();
		return parent::beforeAction($action);
	}

	protected function checkCode()
	{
		/** @var null|Users $user */
		$user = yii::$app->user->isGuest ? null : yii::$app->user->identity;
		$request = yii::$app->request;
		if (in_array($this->id . '/' . $this->action->id, $this->codeRequiredMethods) && $user && $user->isAuthByToken()) {
			$device = UserDevices::getDeviceWithCode($request->get('access_token'));
			if (empty($device)) {
				throw new yii\web\HttpException(428, 'Необходимо смс подтверждение');
			}
			$device->touchCode();
		}
	}

	protected function setEnvironment()
	{
		$request = yii::$app->request;
		/** @var \common\components\services\Environment $environment */
		$environment = Yii::$app->environment;
		$prop = ['mode' => 'guest'];

		if ($request->get('REMOTE_ADDR') !== null) {
			$prop['ip'] = trim($request->get('REMOTE_ADDR'));
		} elseif (isset($_SERVER)) {
			$prop['ip'] = trim(@$_SERVER['HTTP_X_REAL_IP'] . " " . @$_SERVER['REMOTE_ADDR']);
		}
		$environment->setName($environment::MODULE_WEB)->setProp($prop);
		$user = yii::$app->user->isGuest ? null : yii::$app->user->identity;
		if (!empty($user)) {
			$prop['mode'] = 'user';
			if ($user->isAuthByToken()) {
				$environment->setName($environment::MODULE_APP);
				$userDevice = UserDevices::getUserDevice($request->get('access_token'), $user->user_id);
				if (!empty($userDevice)) {
					$prop['device'] = $userDevice->getDeviceType();
				}
			}
			$environment->setProp($prop);
		}
	}
}
