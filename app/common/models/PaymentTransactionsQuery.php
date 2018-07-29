<?php

namespace common\models;

use yii;

/**
 * This is the ActiveQuery class for [[PaymentTransactions]].
 *
 * @see PaymentTransactions
 */
class PaymentTransactionsQuery extends \yii\db\ActiveQuery
{
	protected $tableName;

	public function init()
	{
		parent::init();
		$this->tableName = PaymentTransactions::tableName();
	}

	/**
	 * @param int|array $id можно передавать массив. Тогда будет условие in
	 * @return PaymentTransactionsQuery
	 */
	public function byId($id)
	{
		return $this->andWhere([$this->tableName . '.id' => $id]);
	}

	/**
	 * @param $key
	 * @return $this
	 */
	public function byKey($key)
	{
		if (is_numeric($key)) {
			return $this->byId($key);
		}

		$uuid = substr($key, 0, 36);
		$serviceId  = substr($key, 37);

		return $this->andWhere(
			[
				$this->tableName . '.uuid' => $uuid,
				$this->tableName . '.service_id' => $serviceId
			]);
	}

	/**
	 * @return PaymentTransactionsQuery
	 */
	public function withoutNew()
	{
		return $this->andWhere($this->tableName . '.status != \'' . PaymentTransactions::STATUS_NEW . '\'');
	}

	/**
	 * @return PaymentTransactionsQuery
	 */
	public function currentUser()
	{
		return $this->andWhere([$this->tableName . '.user_id' => Yii::$app->user->id]);
	}

	/**
	 * @param string $uuid
	 * @return PaymentTransactionsQuery
	 */
	public function whereTransaction($uuid)
	{
		return $this->andWhere([$this->tableName . '.uuid' => $uuid]);
	}
	/**
	 * @param int $serviceId
	 * @return PaymentTransactionsQuery
	 */
	public function whereServiceId($serviceId)
	{
		return $this->andWhere([$this->tableName . '.service_id' => $serviceId]);
	}

	/**
	 * @return PaymentTransactionsQuery
	 */
	public function onlyNew()
	{
		return $this->andWhere($this->tableName . '.status = \'' . PaymentTransactions::STATUS_NEW . '\'');
	}

	/**
	 * @return PaymentTransactionsQuery
	 */
	public function onlySuccess()
	{
		return $this->andWhere($this->tableName . '.status = \'' . PaymentTransactions::STATUS_SUCCESS . '\'');
	}

	/**
	 * @return PaymentTransactionsQuery
	 */
	public function onlyFail()
	{
		return $this->andWhere($this->tableName . '.status = \'' . PaymentTransactions::STATUS_FAIL . '\'');
	}

	/**
	 * @return PaymentTransactionsQuery
	 */
	public function onlyInProcess()
	{
		return $this->andWhere($this->tableName . '.status = \'' . PaymentTransactions::STATUS_IN_PROCESS . '\'');
	}

	/**
	 * @inheritdoc
	 * @return PaymentTransactions[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return PaymentTransactions|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}