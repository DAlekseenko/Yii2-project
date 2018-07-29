<?php

namespace common\models;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Users]].
 *
 * @see Users
 */
class UsersQuery extends \common\components\db\ActiveQuery
{

	/**
	 * @param int|array $id можно передавать массив. Тогда будет условие in
	 * @return $this
	 */
	public function byId($id)
	{
		return $this->andWhere([Users::tableName() . '.user_id' => $id]);
	}

	public function byPhone($phone)
	{
		return $this->andWhere([Users::tableName() . '.phone' => $phone]);
	}

	public function byUuid($uuid)
	{
		return $this->andWhere([Users::tableName() . '.subscriber_uuid' => $uuid]);
	}


	public function isBlank()
	{
		return $this->andWhere([Users::tableName() . '.subscription_status' => Users::USER_TYPE_BLANK]);
	}

	public function isReal()
	{
		$u = Users::tableName();
		$s = Users::USER_TYPE_BLANK;
		return $this->andWhere("$u.subscription_status > $s");
	}

	public function withSuccessTransactions()
	{
		$with = [
			'transactions' => function (ActiveQuery $query) {
				$query->andwhere(['status' => PaymentTransactions::STATUS_SUCCESS]);
			},
		];
		return $this->with($with);
	}

	/**
	 * @inheritdoc
	 * @return Users[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return Users|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}

	/**
	 * @return Users|array|null
	 */
	public function oneForUpdate()
	{
		$sql = $this->createCommand()->getRawSql();

		return Users::findBySql($sql . ' FOR UPDATE')->one();
	}
}