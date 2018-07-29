<?php

namespace console\controllers;

use frontend\models\Users;
use PbrLibBelCommon\Caller\WsCaller;
use PbrLibBelCommon\Protocol\BelGai\BelGaiClient;
use PbrLibBelCommon\Protocol\RuniverseSubs\Message\SubscribeInit;
use PbrLibBelCommon\Protocol\RuniverseSubs\RuniverseClient;
use PbrLibBelCommon\Protocol\Utility\MtsbelUtilityClient;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use Yii;
use yii\web\HttpException;
use yii\console\Controller;
use common\models\PaymentTransactions;
use common\components\services\MqJobMessage;
use eripDialog\EdHelper;
use eripDialog\logMessages\EdCancelLogMessage;

/**
 * Class ToolsController
 * @package console\controllers
 */
class ToolsController extends Controller
{
	const LOG = 'rest';

	protected $intervals = [
		60, 60, 180, 300, 600
	];

	public function actionFindEripFail()
	{
		/**	@var PaymentTransactions[] */
		$transactions = PaymentTransactions::find()
			->where(['status' => PaymentTransactions::STATUS_SUCCESS])
			->andWhere(['>', 'date_pay', date('Y-m-01 00:00:00', time())])->all();

		echo 'Find ' . count($transactions) . ", with fail:\n";

		$d = [];
		foreach ($transactions as $transaction) {
			$eripData = $transaction->getEripDataArray();
			if (!isset($eripData['eripResult']['success']) || $eripData['eripResult']['success'] == false) {
				$d[] = $transaction->uuid;
			}
		}
		if (!empty($d)) {
			echo '(\'' . implode("','", $d) . '\')' . "\n";
		}
	}

	public function actionCancel($paymentId)
	{
		$iteration = 0;

		while (isset($this->intervals[$iteration])) {
			try {
				Yii::info('Transaction cancel ' . $paymentId . ', iteration: ' . ($iteration + 1), self::LOG);

				if (EdHelper::eripCancelByPaymentId($paymentId, EdCancelLogMessage::class) == false) {
					throw new HttpException(404, 'Unable to cancel transaction');
				}
				Yii::info('Transaction cancel success (' . $paymentId . ')!', self::LOG);
				return true;

			} catch (HttpException $e) {
				Yii::warning('Unable to cancel transaction (' . $paymentId . '): ' . $e->statusCode . ' ' . $e->getName(), self::LOG);
				sleep($this->intervals[$iteration++]);
			} catch (\Exception $e) {
				Yii::error('Unexpected error (' . $paymentId . '): ' . $e->getMessage(), self::LOG);
				break;
			}
		}
	}

	public function actionTestBelGai()
	{
		$logger = \yii::$app->{LOG_CATEGORY_BEL_GAI};
		$belGaiClient = new BelGaiClient($logger, 'http://86.57.253.58:8020/BCM/rest/Register', 'http://86.57.253.58:8020/BCM/rest/Cancel');

		var_dump($belGaiClient->registerSingleVehicle(
			'846684fd-3a87-4e95-b6ec-b1e29195e694',
			'4250682А058РВО',
			'МАА1762463',
			'7918НХ7',
			'375333092412'
		));
	}

//	public function actionTest()
//	{
//		var_dump( \Yii::$app->getSecurity()->generateRandomString() );
//	}

//	public function actionPush()
//	{
//		$notifications = \common\components\services\NotificationService::androidPushNotification();
//		foreach (Users::find()->where(['is_real' => true])->each() as $item) {
//			echo ('processUser:' . $item->phone . "\n");
//			$notifications->addUserData($item->user_id, ['title' => 'Обновление сервиса МТС Деньги', 'text' => 'Уважаемый абонент! Доступна новая версия приложения МТС Деньги, просьба зайти в Google play и проверить, есть ли у вас последнее обновление']);
//		}
//		$notifications->sendAll();
//	}

//	public function actionUpdateSubscription()
//	{
//		/**	@var Users $item  */
//		foreach (Users::find()->with(['transactions', 'invoicesUsersData', 'paymentFavorites'])->andWhere('subscriber_uuid IS NULL')->orderBy('user_id')->each() as $item) {
//			/** @var \api\components\services\Subscription\SubscriptionClient $subscriptionClient */
//			$subscriptionClient = \yii::$app->{SERVICE_SUBSCRIPTION_CLIENT};
//
//			$subscriberInfo = $subscriptionClient->getPotentialSubscriberInfo($item->phone);
//
//			if ($subscriberInfo === false) {
//				print "cant get subscription info for user: {$item->phone}\n";
//			} else {
//				$item->subscriber_uuid = $subscriberInfo->getSubscriberId();
//				$item->subscription_status = ((int) $item->is_real) * 100;
//				$item->save();
//			}
//
//			usleep(100000);
//		}
//	}

//	public function actionToQueue()
//	{
//		$handler = new InternalQueue(null, '172.17.106.14', 'inform', 'l!j@cneg', 'BelSubs-ToMtsMoneyService');
//
//		$file = file('/home/kirsanov/to_queue.php');
//
//		foreach ($file as $msg) {
//			echo $msg;
//			$handler->pushTaskId($msg);
//			sleep(1);
//		}
//	}

//	public function actionUsersInfo()
//	{
//		$mtsClient = new MtsbelUtilityClient(new NullLogger(), PROCESSING_MTS_BEL_URL . '/mts/query');
//
//		$f = fopen(DATA_DIR . 'data/info-' . date('Y-m-d_H-i-s', time()) . '.csv', 'w');
//		/**	@var Users $item  */
//		foreach (Users::find()->where(['is_real' => true])->each() as $item) {
//			try {
//				$result = $mtsClient->getSubscriberFullInfo($item->phone);
//
//				\fputcsv($f, [
//					$item->id,
//					$item->phone,
//					(int)$result->isMsisdnValid(),
//					(int)$result->isCreditAccount(),
//					(int)$result->getContractNumber(),
//					(int)$result->getBlockingCode(),
//					(float)$result->getTotalBalance()
//				]);
//				usleep(100000);
//			} catch (\Exception $e) {
//				echo($item->phone . "\n");
//			}
//		}
//		fclose($f);
//	}
}
