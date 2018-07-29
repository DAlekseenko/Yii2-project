<?php

namespace common\models;

use yii\db\ActiveRecord;


/**
 * Class Reports
 * @package common\models
 *
 * @property integer $id
 * @property integer $leader_id
 * @property string $file_name
 * @property \DateTime $date_create
 * @property \DateTime $date_concluding
 * @property integer $status
 * @property string $report_maker
 * @property string $path
 *
 * @property  Users $user
 */
class Reports extends ActiveRecord implements \JsonSerializable
{

    const REPORT_REGISTER = 0;
    const REPORT_PERFORMED = 1;
    const REPORT_SUCCESS = 2;
    const REPORT_FAILURE = -1;

    const statusList = [
        self::REPORT_REGISTER => 'Зарегистрирован',
        self::REPORT_PERFORMED => 'Выполняется',
        self::REPORT_SUCCESS => 'Выполнен успешно',
        self::REPORT_FAILURE => 'Выполнен не успешно',
    ];

    public function rules()
    {
        return [
            [['report_maker', 'leader_id', 'file_name', 'path'], 'required'],
            [['path'], 'safe'],
            [['report_maker'], 'string'],
            [['file_name'], 'string', 'max' => 50],
        ];
    }

    public function attributeLabels()
    {
        return [
            'file_name' => 'Имя файла',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['user_id' => 'leader_id']);
    }

    public function jsonSerialize()
    {
        return array_merge(
            $this->getAttributes(),
            ['user' => $this->user->first_name . ' ' . $this->user->last_name]
        );
    }
}