<?php

namespace common\components\services\builders;

use api\models\admin\Users;
use api\models\admin\search\UsersSearch;
use yii\helpers\ArrayHelper;

class UsersReportBuilder extends AbstractReportBuilder
{

    public function __construct(UsersSearch $userSearch)
    {
        $this->search = $userSearch;
        $this->fileName = 'users_csv_' . rand(1000, 9999) . '_' . date('Y-m-d_H-i-s');
        $this->extension = '.csv';
    }

    /**
     * @return array
     */
    public function prepareHeader()
    {
        return [
            'ID',
            'Роль',
            'Телефон',
            'ФИО',
            'Статус пользователя',
            'Дата Регистрации',
            'ID подписчика',
            'Кол-во успешных транзакций',
            'Витрина',
            'IP-адрес'
        ];
    }

    /**
     * @return \common\models\UsersQuery|\yii\db\ActiveQuery
     */
    public function getData()
    {
        return $this->search->frontSearch($this->search->getQuery(), true)->orderBy('user_id');
    }

    /**
     * @param Users $tr
     * @return array
     */
    public function prepareRow($tr)
    {
        return [
            $tr->user_id,
            implode(',', ArrayHelper::map($tr->roles, 'name', 'description')),
            $tr->phone,
            $tr->first_name . ' ' . $tr->last_name . ' ' . $tr->patronymic,
            $tr->subscription_status,
            $tr->date_create,
            $tr->subscriber_uuid,
            $tr->countSuccessTransactions(),
            isset($tr->params[$tr::PARAMS_ON_CREATE_ENVIRONMENT]['name']) ? $tr->params[$tr::PARAMS_ON_CREATE_ENVIRONMENT]['name'] : '',
            isset($tr->params[$tr::PARAMS_ON_CREATE_ENVIRONMENT]['prop']['ip']) ? $tr->params[$tr::PARAMS_ON_CREATE_ENVIRONMENT]['prop']['ip'] : ''
        ];
    }

}