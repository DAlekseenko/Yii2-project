<?php

namespace common\models;

use \yii\db\ActiveRecord;

/**
 * Class DealersEmployees
 * @package common\models
 *
 * @property integer $employee_id
 * @property integer $name
 * @property string $position
 * @property integer $dealer_id
 * @property integer $login
 * @property Dealers $dealer
 *
 */
class DealersEmployees extends ActiveRecord implements \JsonSerializable
{

    const NOT_IN_COMPETITION = [10000393931, 10000381861];

    /** @var ActiveRecord $activeSubs */
    protected $activeSubs;

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmployeesSubscribers()
    {
        return $this->hasMany(DealersSubscribers::className(), ['employee_id' => 'employee_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDealer()
    {
        return $this->hasOne(Dealers::className(), ['id' => 'dealer_id']);
    }

    public function getEmployeesSubscribersCount()
    {
        return $this->getEmployeesSubscribers()->count();
    }

    /**
     * Привлечено абонентов
     * @return int
     */
    public function getTotalPayments()
    {
        $u = Users::tableName();
        $p_h = PaymentTransactionsHistory::tableName();
        $d_a = DealersSubscribers::tableName();

        return $this->getEmployeesSubscribers()
            ->leftJoin($u, "$u.phone::BIGINT = $d_a.subscriber_phone")
            ->leftJoin($p_h, "$p_h.user_id = $u.user_id")
            ->where("$p_h.date_pay >= $u.contract_id_date_change")
            ->andWhere("$p_h.status = 'SUCCESS'")
            ->andWhere(['not in', "$p_h.service_id", self::NOT_IN_COMPETITION])
            ->count("$u.user_id");

    }

    /**
     * Количество абонентов по карте VISA
     * @return int|string
     */
    public function getPayWithVisaSubs()
    {
        $u = Users::tableName();
        $p_h = PaymentTransactionsHistory::tableName();
        $d_a = DealersSubscribers::tableName();
        $a_t = AssistTransactions::tableName();

        $query = self::find()->select("$d_a.employee_id, COUNT(DISTINCT($u.user_id)) as activeSubs")
            ->joinWith('employeesSubscribers')
            ->leftJoin($u, "$u.phone::BIGINT = $d_a.subscriber_phone")
            ->leftJoin($p_h, "$p_h.user_id = $u.user_id")
            ->leftJoin($a_t, "$a_t.user_id = $u.user_id")
            ->andWhere("$p_h.date_pay >= $u.contract_id_date_change")
            ->andWhere("$p_h.status = 'SUCCESS'")
            ->andWhere("$a_t.status = 'Approved'")
            ->andWhere("$a_t.assist_data->'meantypename' = '\"VISA\"'")
            ->groupBy("$d_a.employee_id");


        return $query;
    }


    /**
     * @param $login
     * @return static
     */
    public static function getEmployeeByLogin($login)
    {
        return self::findOne(['login' => "$login"]);
    }

    /**
     * @param null $offset
     * @param $employee $this|null
     * @return \yii\db\ActiveQuery
     */
    public function getSortedData($offset = null, $employee = null)
    {

        $d_e = self::tableName();
        $query = self::find()->select(["$d_e.*" ,  "COALESCE(d_e.activeSubs, 0) AS \"activeSubs\""]);

        if ($employee) {
            $query->joinWith('dealer')->where(['dealers.region' => $employee->dealer->region]);
        }
        $query->leftJoin(['d_e' => $this->getPayWithVisaSubs()], "d_e.employee_id = $d_e.employee_id");

        $query->orderBy("activeSubs DESC, name ASC");

        if (isset($offset)) {
            $query->offset($offset)->limit(50);
        }

        return $query;
    }

    /**
     * @param null $dealerEmployee
     * @return int
     */
    public function getPlace($dealerEmployee = null)
    {
        /** @var self $employee */
        $i = 0;
        foreach ($this->getSortedData(null, $dealerEmployee)->each() ?: [] as $employee) {
            $i++;
            if ($employee->employee_id === $this->employee_id) {
                return $i;
            }
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->employee_id,
            'name' => $this->name,
            'login' => $this->login,
            'position' => $this->position,
            'address' => $this->dealer->address,
            'head' => $this->dealer->head,
            'segment' => $this->dealer->region,
            'countSubscriber' => $this->getEmployeesSubscribersCount(),
            'totalPayments' => $this->getTotalPayments(),
            'activeSubscriber' => $this->activeSubs,
            'commonPlace' => $this->getPlace(),
            'regionPlace' => $this->getPlace($this)
        ];
    }
}