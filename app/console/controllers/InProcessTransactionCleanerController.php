<?php

namespace console\controllers;


use common\models\PaymentTransactions;
use eripDialog\logMessages\EdCancelLogMessage;
use eripDialog\EdHelper as H;

/**
 * Class InProcessTransactionCleanerController
 * @package console\controllers
 */
class InProcessTransactionCleanerController extends AbstractCronTask
{
    /**
     * @param string[] $params
     * @return void
     * @throws \Exception
     */
    public function handler(array $params)
	{
		/**	@var PaymentTransactions[] */
		$transactions = PaymentTransactions::find()->onlyInProcess()->andWhere(['<', 'date_create', date('Y-m-d 00:00:00', time())])->all();
		foreach ($transactions as $transaction) {
			H::eripCancel($transaction, EdCancelLogMessage::class);
			$transaction->status = PaymentTransactions::STATUS_FAIL;
			$transaction->date_pay = date('Y-m-d H:i:s', time());
			$transaction->save();

			if (!empty($transaction->invoice)) {
				$transaction->invoice->delete();
			}
		}
	}
}
