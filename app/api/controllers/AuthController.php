<?php

namespace api\controllers;

use yii;
use yii\web\HttpException;
use common\models\UserDevices;
use common\models\virtual\ApiRegistration;
use api\components\services\Subscription\RegistrationService;
use common\components\services\MqAnswerMessage;
use common\components\services\Helper;

class AuthController extends AbstractController
{
	const RESPONSE_TYPE = 'sdp-auth';

	const MSISDN_HEADER = 'x-WAP-Network-Client-MSISDN';

	/** @var MqAnswerMessage|null  */
	protected $mqAnswerMessage = null;

	public function init()
	{
		$response = Yii::$app->response;
		$response->setResponseType(self::RESPONSE_TYPE);

		$callback = function() use ($response) {
			if (!empty($this->mqAnswerMessage)) {
				$this->mqAnswerMessage->setContent($response->data);
				/** @var \common\components\services\MqConnector $connector*/
				$connector = Yii::$app->amqp;
				$connector->sendMessageDirectly($this->mqAnswerMessage);
				$response->content = '';
			}
		};
		$response->on($response::EVENT_AFTER_PREPARE, $callback);
	}

	public function actionSdp($connection_id, $answer_to, $device_id, $key)
	{
		if (!Helper::checkParamsByKey([$connection_id, $answer_to, $device_id], $key)) {
			throw new HttpException(400);
		}
		$this->mqAnswerMessage = new MqAnswerMessage($connection_id, $answer_to);
		/** @var \common\components\services\Environment $env */
		$env = Yii::$app->environment;
		$env->setName($env::MODULE_APP);
		$env->setProp(['sdp' => true]);

		Yii::info("SDP authorization request");
		/** @todo реализовать проверку на IP */
		
		$phone = Yii::$app->request->headers->get(self::MSISDN_HEADER);
		Yii::info("SDP authorization phone: ($phone)");

		$model = new ApiRegistration();
		$registrationService = new RegistrationService($model);
		if ($registrationService->validate(['phone' => $phone]) === false) {
			throw new HttpException(401, $model->getFirstError('phone'));
		}
		Yii::info("SDP authorization env: " . json_encode($env));
		$user = $registrationService->registerUser($env);
		if ($user === false) {
			throw new HttpException(401, $model->getFirstError('phone'));
		}

		Yii::info("SDP authorization (connection_id: $connection_id, answer_to: $answer_to), phone:  {$user->phone}");
		$device = UserDevices::getDevice($device_id, $user);
		if (empty($device)) {
			throw new \yii\web\ServerErrorHttpException('Ошибка при генерации ключа доступа');
		}

		return ['token' => $device->access_token, 'phone' => $user->phone];
	}
}
