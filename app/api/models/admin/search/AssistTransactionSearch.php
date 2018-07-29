<?php

namespace api\models\admin\search;

use api\models\admin\AdminAssistTransactions;
use common\models\Users;
use yii\base\Model;
use yii\db\ActiveQuery;


class AssistTransactionSearch extends Model
{
    public $phone, $type, $status, $date_create_from, $date_create_to, $last_date, $offset = 0;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone', 'type', 'status', 'date_create_from', 'date_create_to', 'last_date', 'offset'], 'safe'],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getQuery()
    {
        $assist = new AdminAssistTransactions;
        return $assist::find()->joinWith('user')->where(['!=', 'status', $assist::STATUS_NEW]);
    }

    /**
     * @param ActiveQuery $query
     * @param bool $withoutLimit
     * @return ActiveQuery
     */
    public function frontSearch(ActiveQuery $query, $withoutLimit = false)
    {
        $table = AdminAssistTransactions::tableName();
        $user = Users::tableName();

        if ($this->last_date) {
            $query->andFilterWhere(['<=', "$table.date_create", $this->last_date]);
        }

        if (!empty($this->date_create_from)) {
            $query->andFilterWhere(['>=', "$table.date_create", $this->date_create_from]);
        }

        if (!empty($this->date_create_to)) {
            $query->andFilterWhere(['<=', "$table.date_create", $this->date_create_to . ' 23:59:59']);
        }

        if (!empty($this->type)) {
            $query->andFilterWhere(["$table.type" => $this->type]);
        }

        if (!empty($this->status)) {
            $query->andFilterWhere(["$table.status" => $this->status]);
        }

        $query->andFilterWhere(['ilike', "$user.phone", $this->phone]);

        $query->offset($this->offset);

        if (!$withoutLimit) {
            $query->limit(100);
        }

        $query->orderBy('date_create DESC');

        return $query;

    }


}