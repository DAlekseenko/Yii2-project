<?php

namespace common\models;

use \yii\db\ActiveRecord;


/**
 * Class DealersSubscribers
 * @package common\models
 *
 * @property integer $subscriber_phone
 * @property string $invite_date
 * @property integer $employee_id
 *
 */
class DealersSubscribers extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['subscriber_phone', 'employee_id'], 'required'],
            [['subscriber_phone'], 'subscriberPhoneValidator'],
            [['employee_id'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'subscriber_phone' => 'Введите номер абонента',
        ];
    }

    /**
     * @return bool
     */
    public function beforeValidate()
    {
        $this->subscriber_phone = preg_replace('/[^0-9]/', '', $this->subscriber_phone);
        return parent::beforeValidate();
    }

    /**
     * @return bool
     */
    public function subscriberPhoneValidator()
    {
        if (strlen($this->subscriber_phone) != 12) {
            $this->addError('subscriber_phone', 'Неверно заполнен «Номер абонента»');
            return false;
        }

        if ($regUser = Users::find()->where(['phone' => $this->subscriber_phone])->one()) {
            if ($regUser->isReal()) {
                $this->addError('subscriber_phone', 'Абонент уже является подписчиком сервиса!');
                return false;
            }
        }

        if (self::getInviteSubscriber($this->subscriber_phone)) {
            $this->addError('subscriber_phone', 'Абонент уже проинформирован!');
            return false;
        }
        return true;
    }

    /**
     * @param $subscriber_phone
     * @return static
     */
    public static function getInviteSubscriber($subscriber_phone)
    {
        return self::findOne(['subscriber_phone' => $subscriber_phone]);
    }

}