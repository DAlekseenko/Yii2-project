<?php

namespace common\models;

class LimitsQuery extends \common\components\db\ActiveQuery
{
	/**
	* @param  string $userId
	* @return static|null|\common\models\LimitsQuery
	*/
	public function byUserId($userId)
	{
		return $this->andWhere([Limits::tableName() . '.user_id' => $userId]);
	}

	/**
	 * @inheritdoc
	 * @return Limits[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return Limits|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}

	/**
	 * @return Limits|array|null
	 */
	public function oneForUpdate()
	{
		$sql = $this->createCommand()->getRawSql();

		return Limits::findBySql($sql . ' FOR UPDATE')->one();
	}
}