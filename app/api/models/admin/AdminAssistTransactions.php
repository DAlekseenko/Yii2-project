<?php


namespace api\models\admin;


use common\models\AssistTransactions;

/**
 * Class AdminAssistTransactions
 * @package api\models\admin
 */
class AdminAssistTransactions extends AssistTransactions implements \JsonSerializable
{
    const statusList = [
        self::STATUS_IN_PROCESS => 'В процессе',
        self::STATUS_DELAYED => 'Задерживается',
        self::STATUS_APPROVED => 'Утвержден',
        self::STATUS_PARTIAL_APPROVED => 'Частично Утвержден',
        self::STATUS_PARTIAL_DELAYED => 'Частично Задерживается',
        self::STATUS_CANCELED => 'Отменен',
        self::STATUS_PARTIAL_CANCELED => 'Частично Отменен',
        self::STATUS_DECLINED => 'Отказ',
        self::STATUS_TIMEOUT => 'Тайм-аут',
    ];

    const typesList = [
        self::TYPE_RECHARGE => 'Пополнение',
        self::TYPE_INVOICE => 'Начисление',
        self::TYPE_PAYMENT => 'Платеж'
    ];

    public static function getLastDateCreate()
    {
        return self::find()->max('date_create');
    }

    public function jsonSerialize()
    {
        return array_merge($this->getAttributes(), ['phone' => $this->user->phone]);
    }

}