<?php

namespace common\models;

/**
 * @property $user_id integer
 * @property $month_sum float
 * @property $day_sum float
 */
class Limits extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'limits';
	}

	public function setUserId($userId)
	{
		$this->user_id = $userId;

		return $this;
	}

	/**
	 * @return int
	 */
	public static function calculateMonthLimits()
	{
		$from = date('Y-m-01 00:00:00', time());
		$l = self::tableName();
		$lg = LimitsGroup::tableName();
		$h = PaymentTransactionsHistory::tableName();

		$pdo = \yii::$app->db->getMasterPdo();
		$sql = "DELETE FROM $l; DELETE FROM $lg; 
				INSERT INTO $l(user_id, month_sum) SELECT user_id, sum(sum) 
					FROM $h 
					WHERE status='SUCCESS' AND date_create >= '$from'
					GROUP BY user_id;";

		return $pdo->exec($sql);
	}

	/**
	 * @inheritdoc
	 * @return LimitsQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new LimitsQuery(get_called_class());
	}
}
