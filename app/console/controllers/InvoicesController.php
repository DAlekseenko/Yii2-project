<?php

namespace console\controllers;

use yii\console\Controller;
use common\models\PaymentTransactions;
use common\components\services\NotificationService;

/**
 * Class InvoicesController
 * @package console\controllers
 */
class InvoicesController extends Controller
{
    /**
     * @param $uuid
     * @return bool
     */
    public function actionInform($uuid)
	{
		$transaction = PaymentTransactions::find()->onlyNew()->andWhere(['uuid' => $uuid])->one();
		if (empty($transaction)) {
			return false;
		}

		$notifications = NotificationService::newInvoicesNotification();
		$notifications->addUserData($transaction->user_id, $transaction);
		$notifications->sendAll();
	}
}
