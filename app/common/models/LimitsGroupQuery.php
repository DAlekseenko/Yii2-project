<?php

namespace common\models;

class LimitsGroupQuery extends \common\components\db\ActiveQuery
{
	/**
	 * @param $userId
	 * @param $categoryKey
	 * @return $this
	 */
	public function byUserIdAndCategoryValue($userId, $categoryKey)
	{
		$t = LimitsGroup::tableName();
		return $this->andWhere([
			"$t.user_id" => $userId,
			"$t.group_name" => LimitsGroup::LIMIT_GROUP_CATEGORY,
			"$t.group_value" => $categoryKey,
		]);
	}

	public function byUserIdAndGroupName($userId, $groupName)
	{
		$t = LimitsGroup::tableName();
		return $this->andWhere([
		    "$t.user_id" => $userId,
		    "$t.group_name" => $groupName,
	    ]);
	}


	/**
	 * @inheritdoc
	 * @return LimitsGroup[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return LimitsGroup|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}

	/**
	 * @return LimitsGroup|array|null
	 */
	public function oneForUpdate()
	{
		$sql = $this->createCommand()->getRawSql();

		return LimitsGroup::findBySql($sql . ' FOR UPDATE')->one();
	}
}