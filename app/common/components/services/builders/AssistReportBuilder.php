<?php

namespace common\components\services\builders;

use api\models\admin\search\AssistTransactionSearch;
use api\models\admin\Users;
use api\models\admin\AdminAssistTransactions;

class AssistReportBuilder extends AbstractReportBuilder
{

    public function __construct(AssistTransactionSearch $searchModel)
    {
        $this->search = $searchModel;
        $this->fileName = 'assist_csv_' . rand(1000, 9999) . '_' . date('Y-m-d_H-i-s');
        $this->extension = '.csv';
    }

    /**
     * @return array
     */
    public function prepareHeader()
    {
        return [
            'Номер ассиста',
            'Тип',
            'Статус',
            'Сумма',
            'Дата Создания',
            'Дата Оплаты',
            'Телефон',
            'Транзакций',
            'Банк',
            'Держатель карты',
            'e-mail',
            'сообщение',
            'Имя типа',
            'Витрина',
            'IP-адрес'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getData()
    {
        return $this->search->frontSearch($this->search->getQuery(), true);
    }

    /**
     * @param AdminAssistTransactions $tr
     * @return array
     */
    public function prepareRow($tr)
    {
        return [
            $tr->order_number,
            $tr::typesList[$tr->type],
            $tr::statusList[$tr->status],
            $tr->sum,
            $tr->date_create,
            $tr->date_pay,
            $tr->user->phone,
            is_array($tr->data) ? implode(';', $tr->data): '',
            isset($tr->assist_data['issuebank']) ? $tr->assist_data['issuebank'] : '',
            isset($tr->assist_data['cardholder']) ? $tr->assist_data['cardholder'] : '',
            isset($tr->assist_data['email']) ? $tr->assist_data['email'] : '',
            isset($tr->assist_data['customermessage']) ? $tr->assist_data['customermessage'] : '',
            isset($tr->assist_data['meantypename']) ? $tr->assist_data['meantypename'] : '',
            isset($tr->params[Users::PARAMS_ON_CREATE_ENVIRONMENT]['name']) ? $tr->params[Users::PARAMS_ON_CREATE_ENVIRONMENT]['name'] : '',
            isset($tr->params[Users::PARAMS_ON_CREATE_ENVIRONMENT]['prop']['ip']) ? $tr->params[Users::PARAMS_ON_CREATE_ENVIRONMENT]['prop']['ip'] : ''
        ];
    }

}