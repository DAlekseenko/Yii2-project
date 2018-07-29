<?php

namespace common\models;

class PaymentTransactionsHistory extends PaymentTransactions
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'payment_transactions_history';
	}

	/**
	 * @inheritdoc
	 * @return PaymentTransactionsQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new PaymentTransactionsHistoryQuery(get_called_class());
	}

	public function save($runValidation = true, $attributeNames = null)
	{
		return false;
	}

	public function insert($runValidation = true, $attributes = null)
	{
		return false;
	}

	public function delete()
	{
		return false;
	}

	public function update($runValidation = true, $attributeNames = null)
	{
		return false;
	}

    public static function getLastID()
    {
        return self::find()->max('id');
    }
}
