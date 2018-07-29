<?php

namespace console\controllers;

use common\components\services\NotificationService;
use Yii;
use common\models\PaymentTransactions;
use common\components\services\MqCronSmsMessageInput;

/**
 * Крон, который запускается каждый день в определенное время и рассылает абонантам информацию о начислениях,
 * которые создались в результате работы крона CheckInvoicesMultithread
 *
 * Class InvoicesCheckInformerController
 * @package console\controllers
 */
class InvoicesCheckInformerController extends AbstractCronTask
{
    /**
     * @param string[] $params
     * @return void
     */
    public function handler(array $params)
	{
		/** @var \common\components\services\MqConnector $connector*/
		$connector = Yii::$app->amqp;
		$queueManager = $connector->getConnectionManager();

		$notifications = NotificationService::newInvoicesNotification();

		if ($queueManager->countChanelMessages(Yii::$app->params['invoiceCronSmsQueue']) > 0) {
			/** @var  \common\components\services\MqCronSmsMessageInput $request */
			while ($request = $queueManager->getSimple(Yii::$app->params['invoiceCronSmsQueue'], MqCronSmsMessageInput::class)) {
				$transactionIds = $request->getContent();
				$transactions = PaymentTransactions::find()
					->with('service.servicesInfo')
					->where(['uuid' => $transactionIds, 'status' => PaymentTransactions::STATUS_NEW])
					->all();

				if (!empty($transactions)) {
					$notifications->addUserData($transactions[0]->user_id, $transactions, true);
				}
				$queueManager->ackSimple($request);
			}
		}
		$notifications->sendAll();
	}
}
