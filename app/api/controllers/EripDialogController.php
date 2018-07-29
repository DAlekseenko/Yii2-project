<?php

namespace api\controllers;

use eripDialog\exceptions\EdCategoryLimitException;
use eripDialog\exceptions\EdDayLimitException;
use eripDialog\exceptions\EdLimitException;
use eripDialog\exceptions\EdMonthLimitException;
use yii;
use common\models\PaymentTransactions;
use common\components\services\MqJobMessage;
use common\components\filters\RateLimitByKey;
use api\models\exceptions\ModelException;
use api\components\services\MtsProcessing\CommonGwTrait;
use eripDialog\EdApplication;
use eripDialog\EdLogger;
use common\components\services\Environment;
use common\models\Users;
use api\components\services\Subscription\SubscriberHandler;

class EripDialogController extends AbstractController
{
	/** Трейт реализует функционал общения с процессингом */
	use CommonGwTrait;

	public function behaviors()
	{
		return [
			'access' => [
				'class' => yii\filters\AccessControl::className(),
				'rules' => [
					[
						'actions' => ['index'],
						'allow' => true,
					],
					[
						'ips' => ALLOWED_INTERNAL_IPS,
						'allow' => true,
					],
					[
						'allow' => false,
					],
				],
			],
			'rateLimiter' => [
				'class' => yii\filters\RateLimiter::className(),
				'only' => ['index'],
				'user' => Yii::createObject([
												'class' => RateLimitByKey::className(),
												'key' => isset($_SERVER['HTTP_X_REAL_IP'], $_SERVER['REMOTE_ADDR']) ? $_SERVER['HTTP_X_REAL_IP'] . $_SERVER['REMOTE_ADDR'] : Yii::$app->request->get('token'),
												'window' => 60,
												'limit' => 30,
											]),
				'enableRateLimitHeaders' => false,
			],
		];
	}

	public function beforeAction($action)
	{
		$this->enableCsrfValidation = false;
		return parent::beforeAction($action);
	}

	/**
	 * @return array|null
	 * @throws \Exception
	 * @throws \Throwable
	 */
	public function actionIndex()
	{
		$app = EdApplication::getInstance();
		/** @var \api\components\services\ParamsService\ParamsService $paramsService */
		$paramsService = \yii::$app->{SERVICE_PARAMS};

		try {
			$environment = Yii::$app->environment;
			/* @todo костыль, чтобы заставить пользователя обновить апп */
			$module = $environment->getName();
			if ($module == Environment::MODULE_APP && \yii::$app->request->get('version') < 1.3 && !\yii::$app->user->isGuest) {
				/** @var Users $user */
				$user = Yii::$app->user->identity;
				$subscriberHandler = SubscriberHandler::createByUser($user);
				if ($subscriberHandler->isSubscriptionRequired()) {
					yii::$app->response->setResponseMessage('Для проведения платежа необходимо обновить приложение');
					throw new \Exception('update app');
				}
			}
			/* end */
			$logger = new EdLogger('userDialog', 'user_dialog');
			$app->setLogger($logger);
			$app->run(yii::$app->request->get());
			return $app->getStepHandler()->prepareClientOutput();
		} catch (ModelException $e) {

			return $this->returnFieldError($e->getModel());

		} catch (EdLimitException $e) {
			$response = $app->getResponse();
			if ($e instanceof EdMonthLimitException) {
				$response->setError( $paramsService->makeMonthLimitMsg($e->getAvailableSum()) );
			} elseif ($e instanceof EdDayLimitException) {
				$response->setError( $paramsService->makeDayLimitMsg($e->getAvailableSum()) );
			} elseif ($e instanceof EdCategoryLimitException) {
				$response->setError( $paramsService->makeCategoryLimitMsg($e->getAvailableSum(), $e->getCategoryName()) );
			}
			return $response->get();
		}
	}

	/**
	 * Вызывается на успех тарицикации процессингом.
	 *
	 * @param $uuid
	 * @param $oid
	 * @return bool
	 */
	protected function onTariffSuccess($uuid, $oid)
	{
		if (empty($tr = PaymentTransactions::find()->whereTransaction($uuid)->onlyInProcess()->one())) {
			return false;
		}
		$result = $tr->setFinalStatus($oid)->save();
		/** @var \common\components\services\MqConnector $connector */
		$connector = Yii::$app->amqp;
		$connector->sendMessageDirectly(new MqJobMessage(QUEUE_JOBS, 'handlers/mc-call', [$tr->uuid]));
		$connector->sendMessageDirectly(new MqJobMessage(QUEUE_JOBS, 'payment/on-success', [$tr->uuid]));

		return $result;
	}

	/**
	 * Вызывается на ошибку тарицикации процессингом.
	 * @param $uuid
	 * @param $oid
	 * @param $code
	 * @param $description
	 * @return bool
	 */
	protected function onTariffFail($uuid, $oid, $code, $description)
	{
		if (empty($tr = PaymentTransactions::find()->whereTransaction($uuid)->onlyInProcess()->one())) {
			return false;
		}
		$result = $tr->setFinalStatus($oid, false)->setCancelReason($code, $description)->save();
		/** @var \common\components\services\MqConnector $connector */
		$connector = Yii::$app->amqp;
		$connector->sendMessageDirectly(new MqJobMessage(QUEUE_JOBS, 'payment/on-fail', [$tr->uuid]));

		return $result;
	}

	/**
	 * Точка входа для Мобильной Коммерции.
	 *
	 * @deprecated
	 *
	 * @param $transactionID
	 * @param $state
	 * @return bool
	 */
	public function actionMcCallback($transactionID, $state)
	{
		if (empty($tr = PaymentTransactions::find()->whereTransaction($transactionID)->onlyInProcess()->one())) {
			return false;
		}
		/** @var \common\components\services\MqConnector $connector */
		$connector = Yii::$app->amqp;

		Yii::info('Mc call: uuid: ' . $tr->uuid . '; paymentId:  ' . $tr->getPaymentId());

		$tr->status = $state == 'TARIFFED' ? PaymentTransactions::STATUS_SUCCESS : PaymentTransactions::STATUS_FAIL;
		$result = $tr->save();

		if ($tr->status == PaymentTransactions::STATUS_SUCCESS) {
			$connector->sendMessageDirectly(new MqJobMessage(QUEUE_JOBS, 'handlers/mc-call', [$tr->uuid]));
			$connector->sendMessageDirectly(new MqJobMessage(QUEUE_JOBS, 'payment/on-success', [$tr->uuid]));
		} else {
			$connector->sendMessageDirectly(new MqJobMessage(QUEUE_JOBS, 'payment/on-fail', [$tr->uuid]));
		}
		return $result;
	}
}
