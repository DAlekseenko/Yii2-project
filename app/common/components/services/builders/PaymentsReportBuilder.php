<?php

namespace common\components\services\builders;


use api\models\admin\search\PaymentReportSearch;
use common\models\PaymentTransactions;

class PaymentsReportBuilder extends AbstractReportBuilder
{

    private $ruleReportView;

    public function __construct(PaymentReportSearch $paymentReportSearch, $ruleReportView)
    {
        $this->search = $paymentReportSearch;
        $this->ruleReportView = $ruleReportView ? 'WithAccess' : 'WithoutAccess';
        $this->fileName = 'payment_csv_' . rand(1000, 9999) . '_' . date('Y-m-d_H-i-s');
        $this->extension = '.csv';
    }

    /**
     * @return array
     */
    public function prepareHeader()
    {
        $getHeader = 'header' . $this->ruleReportView;
        return $this->$getHeader();
    }

    /**
     * @return \common\models\PaymentTransactionsQuery|null
     */
    public function getData()
    {
        return $this->search->applyFilters()->applyOrders()->getQuery();
    }

    /**
     * @param PaymentTransactions $tr
     * @return array
     */
    public function prepareRow($tr)
    {
        $getRow = 'row' . $this->ruleReportView;
        return $this->$getRow($tr);

    }

    private function headerWithoutAccess()
    {
        return [
            'id',
            'uuid',
            'Статус',
            'Дата платежа в банке',
            'Erip ID',
            'ID сервиса',
            'Имя сервиса',
            'Пользователь',
            'Сумма',
            'Поля',
            'Верификация',
            'Bgate ID',
            'Дата списания МТС',
            'Витрина',
            'Команда',
            'Код',
            'IP-адрес'
        ];
    }

    private function headerWithAccess()
    {
        return [
            'id',
            'uuid',
            'Статус',
            'Дата платежа в банке',
            'ERIP ID',
            'ID сервиса',
            'Имя сервиса',
            'Пользователь',
            'Сумма',
            '% нижней комиссии',
            'Размер нижней комиссии',
            'Доход',
            'Поля',
            'Верификация',
            'Bgate ID',
            'Дата списания МТС',
            'Витрина',
            'Команда',
            'Код',
            'IP-адрес'
        ];
    }

    /**
     * @param PaymentTransactions $tr
     * @return array
     */
    private function rowWithoutAccess($tr)
    {
        return [
            $tr->id,
            $tr->uuid,
            PaymentTransactions::$statusesName[$tr->status],
            $tr->bank_date_create,
            $tr->erip_payment_id,
            $tr->service_id,
            $tr->service->name,
            $tr->user->phone,
            number_format($tr->sum, 2, ',', ''),
            $tr->fieldToExport(),
            (int)$tr->verify_status,
            $tr->bgate_order_id,
            $tr->date_create_mts,
            isset($tr->transaction_params[$tr::PARAMS_ON_PAY_ENVIRONMENT]['name']) ? $tr->transaction_params[$tr::PARAMS_ON_PAY_ENVIRONMENT]['name'] : '',
            isset($tr->transaction_params[$tr::PARAMS_ON_PAY_ENVIRONMENT]['prop']['module']) ? $tr->transaction_params[$tr::PARAMS_ON_PAY_ENVIRONMENT]['prop']['module'] : '',
            isset($tr->transaction_params[$tr::PARAMS_ON_PAY_ENVIRONMENT]['prop']['code']) ? $tr->transaction_params[$tr::PARAMS_ON_PAY_ENVIRONMENT]['prop']['code'] : '',
            isset($tr->transaction_params[$tr::PARAMS_ON_PAY_ENVIRONMENT]['prop']['ip']) ? $tr->transaction_params[$tr::PARAMS_ON_PAY_ENVIRONMENT]['prop']['ip'] : ''
        ];
    }

    /**
     * @param PaymentTransactions $tr
     * @return array
     */
    private function rowWithAccess($tr)
    {
        return [
            $tr->id,
            $tr->uuid,
            PaymentTransactions::$statusesName[$tr->status],
            $tr->bank_date_create,
            $tr->erip_payment_id,
            $tr->service_id,
            $tr->service->name,
            $tr->user->phone,
            number_format($tr->sum, 2, ',', ''),
            number_format($tr->service->provider_fee, 3, ',', ''),
            number_format($tr->getRes(), 10, ',', ''),
            number_format($tr->getRes() - $tr->getTax(), 10, ',', ''),
            $tr->fieldToExport(),
            (int)$tr->verify_status,
            $tr->bgate_order_id,
            $tr->date_create_mts,
            isset($tr->transaction_params[$tr::PARAMS_ON_PAY_ENVIRONMENT]['name']) ? $tr->transaction_params[$tr::PARAMS_ON_PAY_ENVIRONMENT]['name'] : '',
            isset($tr->transaction_params[$tr::PARAMS_ON_PAY_ENVIRONMENT]['prop']['module']) ? $tr->transaction_params[$tr::PARAMS_ON_PAY_ENVIRONMENT]['prop']['module'] : '',
            isset($tr->transaction_params[$tr::PARAMS_ON_PAY_ENVIRONMENT]['prop']['code']) ? $tr->transaction_params[$tr::PARAMS_ON_PAY_ENVIRONMENT]['prop']['code'] : '',
            isset($tr->transaction_params[$tr::PARAMS_ON_PAY_ENVIRONMENT]['prop']['ip']) ? $tr->transaction_params[$tr::PARAMS_ON_PAY_ENVIRONMENT]['prop']['ip'] : ''
        ];
    }

}