<?php
/**
 * Created by PhpStorm.
 * User: kirsanov
 * Date: 21.02.2018
 * Time: 14:58
 */

namespace console\controllers;

use yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use common\models\PaymentTransactions;
use common\components\services\MqJobMessage;
use PbrLibBelCommon\Caller\WsCaller;
use eripDialog\EdHelper;

class VerificationController extends Controller
{
	public function actionIndex()
	{
		$lastDay = time() - 60 * 60 * 24;
		$dateStart = date('Y-m-d 00:00:00', $lastDay);

		yii::info('Run bank verification master process', LOG_CATEGORY_BANK_VERIFY);
		try {
			/** @var PaymentTransactions[] $transactions */
			$transactions = PaymentTransactions::find()
				->where(['status' => PaymentTransactions::STATUS_SUCCESS])
				->andWhere('verify_status IS NULL')
				->andWhere(['>=', 'bank_date_create', $dateStart])
				->andWhere(['<=', 'bank_date_create', date('Y-m-d 23:59:59', $lastDay)])
				->orderBy('bank_date_create')->all();

			for ($i = 0; $i < count($transactions); $i += 30) {

				$list = array_slice($transactions, $i, 30);
				if (!empty($list)) {
					$dateEnd = $list[count($list)-1]->bank_date_create;
					if (!isset($transactions[$i + 30])) { 				// Если нет следующего сета, то ставим дату окончания суток.
						$dateEnd = date('Y-m-d 23:59:59', $lastDay);
					}

					/** @var \common\components\services\MqConnector $connector*/
					$connector = Yii::$app->amqp;
					$connector->sendMessageDirectly(
						new MqJobMessage(QUEUE_SEQUENTIAL_JOBS, 'verification/verify',
							[base64_encode($dateStart), base64_encode($dateEnd), base64_encode(json_encode($list))]
						)
					);
					$dateStart = $list[count($list)-1]->bank_date_create;
				}
			}

		} catch (\Exception $e) {
			yii::error("Bank verification error:\n" . $e->getMessage(), LOG_CATEGORY_BANK_VERIFY);
		}
		yii::info('Complete bank verification master process', LOG_CATEGORY_BANK_VERIFY);
	}

	public function actionVerify($start, $end, $base64List, $ttl = 60)
	{
		$dateStart = base64_decode($start);
		$dateEnd = base64_decode($end);
		$list = base64_decode($base64List);
		try {
			yii::info("Run verification subprocess ({$dateStart} - {$dateEnd}):", LOG_CATEGORY_BANK_VERIFY);
			yii::info($list, LOG_CATEGORY_BANK_VERIFY);

			$decodedList = json_decode($list, 1);
			$uuids = ArrayHelper::getColumn($decodedList,'uuid') ?: [];
			$transactions = PaymentTransactions::find()->where(['uuid' => $uuids])->all();

			$caller = new WsCaller(EdHelper::getEripApiUrl());
			$caller
				->setGetParameter('mode', 'verify')
				->setGetParameter('paymentsData', $list)
				->setGetParameter('paymentsDateStart', $dateStart)
				->setGetParameter('paymentsDateEnd', $dateEnd)
			;
			$result = $caller->callDecodeJson();

			yii::info($result, LOG_CATEGORY_BANK_VERIFY);

			if (!isset($result['success']) || $result['success'] !== true) {
				throw new \Exception('Erip call error');
			}
			if (isset($result['cancelTransactions'])) {
				Yii::info('Verification; transactions with status -3:', LOG_CATEGORY_BANK_VERIFY);
				Yii::info($result['cancelTransactions'], LOG_CATEGORY_BANK_VERIFY);
				foreach ($result['cancelTransactions'] as $paymentId => $v) {
					/** @var \common\components\services\MqConnector $connector*/
					$connector = Yii::$app->amqp;
					$connector->sendMessageDirectly(new MqJobMessage(Yii::$app->params['jobsQueue'], 'tools/cancel', [$paymentId]));
				}
			}
			foreach ($transactions as $transaction) {
				if (isset($result['paymentsStatuses'][$transaction->getPaymentId()])) {
					$verifyData = $result['paymentsStatuses'][$transaction->getPaymentId()];
					if (isset($verifyData['status'])) {
						$transaction->verify_status = $verifyData['status'] == 1
							? PaymentTransactions::VERIFY_STATUS_SUCCESS
							: PaymentTransactions::VERIFY_STATUS_FAIL;

						switch ($verifyData['status']) {
							case 1:
								if ($transaction->getEripDataArray('eripResult.success') === false) {
									$eripData = $transaction->getEripDataArray();
									$eripData['eripResult']['success'] = true;
									$eripData['eripResult']['note'] = 'Результат восстановлен по результатам квитования';
									unset($eripData['eripResult']['code']);
									unset($eripData['eripResult']['errors']);
									$transaction->erip_data = $eripData;
									Yii::warning('Find transaction with erip empty result: ' . $transaction->uuid . '; Change to success.', LOG_CATEGORY_BANK_VERIFY);
								}
								break;
						}
						$transaction->save();
					}
				}
			}
			yii::info("End verification subprocess ({$dateStart} - {$dateEnd}):", LOG_CATEGORY_BANK_VERIFY);
		} catch (\Exception $e) {
			if ($ttl) {
				/** @var \common\components\services\MqConnector $connector */
				$connector = \Yii::$app->amqp;
				$connector->sendMessageDirectly(new MqJobMessage(QUEUE_JOBS_DELAY_1MIN, 'verification/verify', [$start, $end, $base64List, --$ttl]));
			}
		}
	}
}