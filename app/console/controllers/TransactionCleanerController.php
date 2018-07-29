<?php

namespace console\controllers;


use eripDialog\EdHelper;
use eripDialog\logMessages\EdCancelLogMessage;
use common\models\Invoices;
use common\models\PaymentTransactions;
use Yii;

/**
 * Class TransactionCleanerController
 * @package console\controllers
 */
class TransactionCleanerController extends AbstractCronTask
{
	public function init()
	{
		Yii::beginProfile('Cleaning incomplete transactions.');
		parent::init();
	}

    /**
     * @param string[] $params
     * @return void
     * @throws \Exception
     */
    public function handler(array $params)
	{
		$start = microtime(true);

		$deletedTransactions = 0;
		$canceledPayments = 0;

		$transactions = PaymentTransactions::find()->joinWith('invoice')
							->where(Invoices::tableName() . '.id IS NULL')
							->andWhere(['<', PaymentTransactions::tableName() . '.date_create', date('Y-m-d H:i:s', time() - 60 * 30)])
							->onlyNew()
							->all();

		foreach ($transactions as $transaction) {
			$cancelResult = EdHelper::eripCancel($transaction, EdCancelLogMessage::class);

			if ($cancelResult) {
				$canceledPayments++;
				$deletedTransactions += (int) $transaction->delete();
			}
		}
		Yii::endProfile(
			sprintf(
				'Cleaning incomplete transactions done. ' .
				'Time: %.3fs; processedTransactions : %d ; deletedTransactions: %d ; canceledPayments: %d ',
				microtime(true) - $start,
				count($transactions),
				$deletedTransactions,
				$canceledPayments
			)
		);
	}
}
