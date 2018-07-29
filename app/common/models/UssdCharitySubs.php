<?php
/**
 * Created by PhpStorm.
 * User: kirsanov
 * Date: 01.02.2018
 * Time: 16:22
 */

namespace common\models;

/**
 * @property int $id
 * @property string $uuid
 * @property int $msisdn
 * @property string $init_date
 * @property string $start_date
 * @property string $stop_date
 *
 * @property Ussd ussd
 */
class UssdCharitySubs extends AbstractModel
{
	/**
	 * @param $uuid
	 * @param $msisdn
	 * @return array|null|\yii\db\ActiveRecord|self
	 */
	public static function findUserSubscription($uuid, $msisdn)
	{
		return self::find()
				   ->where(['uuid' => $uuid, 'msisdn' => $msisdn])
				   ->andWhere('stop_date IS NULL')->one();
	}

	/**
	 * @param $msisdn
	 * @return array|\yii\db\ActiveRecord[]
	 */
	public static function findActiveUserSubscriptions($msisdn)
	{
		return self::find()
				   ->where(['msisdn' => $msisdn])
				   ->andWhere('stop_date IS NULL')->all();
	}

	/**
	 * @param $uuid
	 * @param $plug
	 * @param $code
	 * @param $msisdn
	 * @return false|UssdCharitySubs
	 * @throws \Exception
	 */
	public static function createSubscript($uuid, $msisdn)
	{
		$sub = new self();
		$sub->uuid = $uuid;
		$sub->msisdn = $msisdn;

		if (!$sub->insert()) {
			return false;
		}
		return $sub;
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getUssd()
	{
		return $this->hasOne(Ussd::className(), ['uuid' => 'uuid']);
	}
}