<?php

namespace common\models;

use yii\helpers\ArrayHelper;

/**
 * @property $user_id int
 * @property $group_name string
 * @property $group_value string
 * @property $sum int
 */
class LimitsGroup extends \yii\db\ActiveRecord
{
	const LIMIT_GROUP_CATEGORY = 'category';

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'group_limits';
	}

	/**
	 * Возвращает массив, где ключами являются hash категории, а значением - сумма, которую пользователь
	 * заплатил по сервисам из этой категории.
	 *
	 * @param  int $userId
	 * @return array
	 */
	public static function getUserCategorySums($userId)
	{
		$limits = self::find()->byUserIdAndGroupName($userId, self::LIMIT_GROUP_CATEGORY)->all();

		if (empty($limits)) {
			return [];
		}

		return array_combine(
			ArrayHelper::getColumn($limits, 'group_value'),
			ArrayHelper::getColumn($limits, 'sum')
		);
	}

    public function setUserIdAndCategoryValue($userId, $categoryKey) 
	{
		$this->user_id = $userId;
		$this->group_name = self::LIMIT_GROUP_CATEGORY;
		$this->group_value = $categoryKey;
		
		return $this;
	}
		
	/**
	 * @inheritdoc
	 * @return LimitsGroupQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new LimitsGroupQuery(get_called_class());
	}
}
