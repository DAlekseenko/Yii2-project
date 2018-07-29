<?php

namespace common\models\validators;

use yii\validators\Validator;

class PaymentTransactionsSumValidator extends Validator
{
	/**
	 * @param \yii\base\Model|\common\models\PaymentTransactions $paymentTransaction
	 * @param string $attribute
	 */
	public function validateAttribute($paymentTransaction, $attribute)
	{
		/** @todo разделять текстовку на  */
		if ($paymentTransaction->status == $paymentTransaction::STATUS_IN_PROCESS) {
			$allowedSum = $paymentTransaction->getMaxSum();
			if (isset($allowedSum) && $allowedSum < $paymentTransaction->sum) {
				$this->addError($paymentTransaction, $attribute, 'Сумма платежа не может превышать ' . $allowedSum . ' руб');
				return;
			}
		}
	}
}
