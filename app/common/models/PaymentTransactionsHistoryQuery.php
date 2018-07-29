<?php

namespace common\models;

class PaymentTransactionsHistoryQuery extends PaymentTransactionsQuery
{
	public function init()
	{
		parent::init();
		$this->tableName = PaymentTransactionsHistory::tableName();
	}

	/**
	 * @inheritdoc
	 * @return PaymentTransactionsHistory[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return PaymentTransactionsHistory|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
