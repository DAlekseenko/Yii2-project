<?php

namespace api\controllers;

use common\models\PaymentTransactions;
use common\models\UssdCharitySubs;
use PbrLibBelCommon\Protocol\RuniverseSubs\Message\ResponseMessage;
use PbrLibBelCommon\Protocol\RuniverseSubs\Message\SubscribeEnd;
use PbrLibBelCommon\Protocol\RuniverseSubs\Message\SubscribeInit;
use yii;
use common\components\services\MqJobMessage;
use common\models\Ussd;
use Psr\Log\NullLogger;
use api\components\services\Charity\CharityRuniverseHandler;
use PbrLibBelCommon\Protocol\RuniverseSubs\RuniverseClient;

class ImmoController extends AbstractController
{
	const LOG_USSD = 'ussd';

	const USSD_PLUG_SMS = 'charity_sms_2121';
	const USSD_PLUG_USSD = 'charity_ussd';

	public function behaviors()
	{
		return [
			'access' => [
				'class' => yii\filters\AccessControl::className(),
				'rules' => [
					[
						'ips'   => ALLOWED_INTERNAL_IPS,
						'allow' => true,
					],
					[
						'allow' => false,
					],
				],
			],
		];
	}

	/**
	 * Возвращает все успешные транзакции за указанный период времени. Используется для сверок.
	 *
	 * @param $timestamp_from
	 * @param $timestamp_to
	 * @return array
	 */
	public function actionGetSuccess($timestamp_from, $timestamp_to)
	{
		$transactions = PaymentTransactions::find()
			->where(['status' => PaymentTransactions::STATUS_SUCCESS])
			->andWhere(['>=', 'date_pay', date('Y-m-d H:i:s', $timestamp_from)])
			->andWhere(['<=', 'date_pay', date('Y-m-d H:i:s', $timestamp_to)])
			->all();

		$result = [];
		foreach ($transactions as $transaction) {
			if ($transaction->getEripDataArray('eripResult.success', false) == true) {
				$result[] = $transaction;
			}
		}
		PaymentTransactions::$serializeMode = PaymentTransactions::SERIALIZE_MODE_CHECK_REGISTRY;

		return ['list' => $result, 'count' => count($result), 'total' => count($transactions)];
	}

	/**
	 * Обновляет информацию о транзакции после прохождения сверок с МТС.
	 *
	 * @param $uuid
	 * @param $bgate_order_id
	 * @param $is_in_mts_registry
	 * @param $mts_registry_date
	 * @return int
	 */
	public function actionUpdateTransactionAfterRegistry($uuid, $bgate_order_id, $is_in_mts_registry, $mts_registry_date)
	{

		$tr = PaymentTransactions::find()->where(['uuid' => $uuid])->one();
		if (empty($tr)) {
			return 0;
		}
		$tr->is_in_mts_register = (bool) (int) $is_in_mts_registry;
		$tr->mts_register_status = $tr->is_in_mts_register ? $tr::MTS_REGISTRY_STATUS_OK : $tr::MTS_REGISTRY_STATUS_NO_IN_MTS;
		$tr->date_create_mts = empty($mts_registry_date) ? null : $mts_registry_date;

		return (int) $tr->save();
	}

	public function actionUssdList($plug)
	{
		$result = Ussd::find()->where(['plug' => $plug])->all();
		usort($result, function($a, $b) {
			return (int) $a->code >= (int) $b->code ? 1 : -1;
		});
		return $result;
	}

	/**
	 * @param $msisdn
	 * @param $plug
	 * @param $code
	 * @return array
	 * @throws yii\web\NotFoundHttpException
	 */
	public function actionUssdGetFundProps($msisdn, $plug, $code)
	{
		$ussd = $this->getUssdRule($plug, $code);

		$result = [
			['method' => 'ussd-pay-by-phone', 'menu' => 'Оплатить']
		];
		$subs = UssdCharitySubs::findUserSubscription($ussd->uuid, $msisdn);

		if (empty($subs) && $ussd->subs_enable) {
			array_push($result, ['method' => 'ussd-start-subscription', 'menu' => 'Подключить автоплатеж']);
		} elseif (!empty($subs) && !$subs->stop_date && $subs->start_date) {
			array_push($result, ['method' => 'ussd-end-subscription', 'menu' => 'Отключить автоплатеж']);
		}
		return $result;

	}

	/**
	 * @param $msisdn
	 * @param $plug
	 * @param $code
	 * @return int
	 * @throws yii\db\StaleObjectException
	 * @throws yii\web\HttpException
	 * @throws yii\web\NotFoundHttpException
	 * @throws \Exception
	 */
	public function actionUssdStartSubscription($msisdn, $plug, $code)
	{
		$ussd = $this->getUssdRule($plug, $code);
		$subs = UssdCharitySubs::findUserSubscription($ussd->uuid, $msisdn);

		if ($ussd->subs_enable === false) {
			throw new yii\web\HttpException(403, 'Подписка для данного сервиса запрещена');
		}
		if (!empty($subs)) {
			throw new yii\web\HttpException(403, 'Подписка уже оформлена или в процессе оформления');
		}
		$message = new SubscribeInit($msisdn, $ussd->uuid);
		$newSub = UssdCharitySubs::createSubscript($ussd->uuid, $msisdn);
		if ($newSub === false) {
			throw new yii\web\HttpException(500, 'Ошибка оформления подписки');
		}
		/** @var RuniverseClient $runiverseClient */
		$runiverseClient = \yii::$app->{SERVICE_RUNIVERSE_CLIENT};
		$response = $runiverseClient->callInit($message);
		if ($response->getCode() === ResponseMessage::CODE_SUCCESS) {
			return 1;
		}
		$newSub->delete();
		throw new yii\web\HttpException(500, 'Ошибка оформления подписки');
	}

	/**
	 * @param $msisdn
	 * @param $plug
	 * @param $code
	 * @return int
	 * @throws yii\web\HttpException
	 */
	public function actionUssdEndSubscription($msisdn, $plug, $code)
	{
		$ussd = $this->getUssdRule($plug, $code);
		$subs = UssdCharitySubs::findUserSubscription($ussd->uuid, $msisdn);
		if (empty($subs)) {
			throw new yii\web\HttpException(404, 'Подписка не найдена');
		}
		$message = new SubscribeEnd($msisdn, $ussd->uuid);
		/** @var RuniverseClient $runiverseClient */
		$runiverseClient = \yii::$app->{SERVICE_RUNIVERSE_CLIENT};
		$response = $runiverseClient->callEnd($message);
		if ($response->getCode() === ResponseMessage::CODE_SUCCESS) {
			return 1;
		}
		throw new yii\web\HttpException(500, 'Ошибка отключения подписки');
	}

	/**
	 * @param $msisdn
	 * @param $plug
	 * @param string $code
	 * @return bool
	 * @throws yii\web\NotFoundHttpException
	 */
	public function actionUssdPayByPhone($msisdn, $plug, $code = '')
	{
		$ussd = $this->getUssdRule($plug, $code);

		try {
			/** @var \common\components\services\MqConnector $connector */
			$connector = Yii::$app->amqp;
			$connector->sendMessageDirectly(new MqJobMessage(QUEUE_SEQUENTIAL_JOBS, 'handlers/pay-by-phone', [
				(int) $msisdn,
				$ussd->plug,
				$ussd->code
			]));
			return true;
		} catch (\Exception $e) {
			yii::info("CHARITY PAYMENT ERROR {$e->getMessage()}", 'rest');
			return false;
		}
	}

	public function actionUssdGetTotalSum($plug, $code = '')
	{
		/** @var Ussd $ussd */
		$ussd = Ussd::find()->where(['plug' => $plug, 'code' => $code])->orWhere(['plug' => $plug, 'code' => '*'])->one();

		return empty($ussd) ? null : $ussd->total;
	}

	/**
	 * Точка входа для Runiverse подписок по благотворительности.
	 *
	 * @param $method
	 * @return int
	 * @throws \PbrLibBelCommon\Exceptions\RuniverseException
	 * @throws yii\web\HttpException
	 */
	public function actionRuniverse($method)
	{
		$post = \yii::$app->request->getRawBody();
		if (empty($post)) {
			throw new yii\web\HttpException(400);
		}
		$logger = \yii::$app->{LOG_CATEGORY_RUNIVERSE_SUBS};

		$handler = new CharityRuniverseHandler($logger);

		$handler->handleMessage($method, $post);

		return 1;
	}

	/**
	 * @param $plug
	 * @param $code
	 * @return Ussd
	 * @throws yii\web\NotFoundHttpException
	 */
	protected function getUssdRule($plug, $code)
	{
		/** @var Ussd $ussd */
		$ussd = Ussd::find()->where(['plug' => $plug, 'code' => $code])->orWhere(['plug' => $plug, 'code' => '*'])->one();
		if (empty($ussd)) {
			throw new yii\web\NotFoundHttpException('Ussd rule was not found');
		}
		return $ussd;
	}

}
