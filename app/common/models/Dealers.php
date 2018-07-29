<?php

namespace common\models;

use \yii\db\ActiveRecord;


/**
 * Class Dealers
 * @package common\models
 *
 * @property integer $id
 * @property string $head
 * @property string $address
 * @property string $region
 * @property DealersEmployees [] $dealersEmployees
 */
class Dealers extends ActiveRecord
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDealersEmployees()
    {
        return $this->hasMany(DealersEmployees::className(), ['dealer_id' => 'id']);
    }

    /**
     * @param $address
     * @return static
     */
    public static function getDealerByAddress($address)
    {
        return self::findOne(['address' => $address]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDealersSubscribers()
    {
        return $this->hasMany(DealersSubscribers::className(), ['employee_id' => 'employee_id'])->via('dealersEmployees');
    }


    public function getDealersSubscribersCount()
    {
        return $this->getDealersSubscribers()->count();
    }


}