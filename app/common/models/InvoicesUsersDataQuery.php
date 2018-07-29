<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[InvoicesUsersData]].
 *
 * @see InvoicesUsersData
 */
class InvoicesUsersDataQuery extends \yii\db\ActiveQuery
{

	/**
	 * @param int|array $id можно передавать массив. Тогда будет условие in
	 * @return $this
	 */
	public function byId($id)
	{
		return $this->andWhere([InvoicesUsersData::tableName() . '.id' => $id]);
	}

	/**
	 * @param int|array $id можно передавать массив. Тогда будет условие in
	 * @return $this
	 */
	public function byServiceId($id)
	{
		return $this->andWhere([InvoicesUsersData::tableName() . '.service_id' => $id]);
	}

	/**
	 * @param int|array $id можно передавать массив. Тогда будет условие not in
	 * @return $this
	 */
	public function ignoreServiceId($id)
	{
		return $this->andWhere(['not in', InvoicesUsersData::tableName() . '.service_id', (array)$id]);
	}

	/**
	 * @return InvoicesUsersDataQuery
	 */
	public function currentUser()
	{
		return $this->andWhere([InvoicesUsersData::tableName() . '.user_id' => \Yii::$app->user->id]);
	}

	/**
	 * @return $this
	 */
	public function notExpired()
	{
		$t = InvoicesUsersData::tableName();

		return $this->andWhere(['>', "$t.date_create", date('Y-m-d H:i:s', time() - 60 * 60 * 24 * 50)]);
	}

	/**
	 * @inheritdoc
	 * @return InvoicesUsersData[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return InvoicesUsersData|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}