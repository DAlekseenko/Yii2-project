<?php

namespace api\models\admin\search;

use common\models\PaymentTransactionsHistory as PaymentTransactions;
use common\models\Users;

class PaymentReportSearch
{
    protected $params = [];

    protected $query = null;

    public function __construct($params = null)
    {
        PaymentTransactions::$serializeMode = PaymentTransactions::SERIALIZE_MODE_FRONT_END;
        $this->params = $params;
        $this->query = PaymentTransactions::find()->joinWith(['service', 'user'])->with(['invoice'])->withoutNew();
    }

    /**
     * @return self
     */
    public function applyFilters()
    {
        $params = $this->params;

        $table = PaymentTransactions::tableName();
        $users = Users::tableName();

        if (isset($params['date_create_to'])) {
            $dateTo = date('Y-m-d 23:59:59', strtotime($params['date_create_to']));
        }

        if (isset($params['startID'])) {
            $this->query->andFilterWhere(['<=', "$table.id", (int)$params['startID']]);
        }

        if (isset($params['id'])) {
            $this->query->andWhere("$table.id IN({$params['id']})");
        }

        if (isset($params['date_create_from'])) {
            $filter = [
                '>=',
                $table . '.date_create',
                date('Y-m-d', strtotime($params['date_create_from'])),
            ];
            $this->query->andFilterWhere($filter);
        }

        if (isset($dateTo)) {
            $filter = [
                '<=',
                $table . '.date_create',
                $dateTo,
            ];
            $this->query->andFilterWhere($filter);
        }

        if (!empty($params['service_id'])) {
            $this->query->andFilterWhere(["$table.service_id" => (int)$params['service_id']]);
        }
        if (!empty($params['uu_id'])) {
            $this->query->andFilterWhere(["$table.uuid" => $params['uu_id']]);
        }
        if (!empty($params['phone'])) {
            $this->query->andFilterWhere(["$users.phone" => (int)$params['phone']]);
        }
        if (!empty($params['erip_id'])) {
            $this->query->andFilterWhere(["$table.erip_payment_id" => (int)$params['erip_id']]);
        }
        return $this;
    }

    /**
     * @return \common\models\PaymentTransactionsQuery|null
     */
    public function getQuery()
    {
        return $this->query;
    }

    public function applyOrders()
    {
        $params = $this->params;

        if (!empty($params['sort'])) {
            if ($params['sort'][0] == '-') {
                $order = ' asc';
                $sort = substr($params['sort'], 1);
            } else {
                $order = ' desc';
                $sort = $params['sort'];
            }
        }

        if (isset($sort, $order)) {
            $this->query->orderBy($sort . $order);
        } else {
            $this->query->orderBy('id desc');
        }

        return $this;
    }

}
