<?php

namespace api\models\admin\search;


use common\components\services\Environment;
use common\models\InvoicesUsersData;
use common\models\PaymentTransactionsHistory;
use api\models\admin\Users;
use common\models\UsersQuery;
use \yii\base\Model;
use yii\db\ActiveQuery;


class UsersSearch extends Model
{

    const TARGET_CHARITY_USERS = 'charity';
    const TARGET_APP_USERS = Environment::MODULE_APP;
    const TARGET_WEB_USERS = Environment::MODULE_WEB;
    const TARGET_USSD_USERS = Environment::MODULE_USSD;

    public static $userTypes = [
        self::TARGET_CHARITY_USERS => 'Благотворительность',
        self::TARGET_APP_USERS => 'Приложение',
        self::TARGET_WEB_USERS => 'Веб сайт',
        self::TARGET_USSD_USERS => 'USSD'
    ];

    public static $subscriptionTypes = [
        Users::USER_TYPE_BLANK => 'Не зарегистрирован',
        Users::USER_TYPE_USER => 'Пользователь',
        Users::USER_TYPE_UNSUBSCRIBES => 'Отписывается',
        Users::USER_TYPE_SUBSCRIBER => 'Подписчик',
        Users::USER_TYPE_BLOCKED => 'В блокировке'
    ];


    public $user_id;

    public $role;

    public $phone;

    public $date_to;

    public $date_from;

    public $type;

    public $payment_from;

    public $payment_to;

    public $payment_count_min;

    public $payment_count_max;

    public $invoice_count_min;

    public $invoice_count_max;

    public $subscription;

    public $offset = 0;

    public static $multiSearchVal = [
        'role',
        'subscription'
    ];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'phone'], 'integer'],
            [['role', 'user_id', 'phone', 'date_to', 'date_from', 'offset', 'type', 'payment_from', 'payment_to', 'payment_count_min', 'payment_count_max', 'invoice_count_min', 'invoice_count_max', 'subscription'], 'safe']
        ];
    }

    /**
     * @return ActiveQuery|UsersQuery
     */
    public function getQuery()
    {
        return Users::find()->with(['roles', 'userDevices'])->withSuccessTransactions();
    }

    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            $this->prepareSearchValue();
            return true;
        }
        return false;
    }

    public function prepareSearchValue()
    {
        foreach (self::$multiSearchVal as $field) {
            if (isset($this->$field)) {
                $prepare = explode(',', $this->$field);
                foreach ($prepare as $k => $name) {
                    $prepare[$k] = \Yii::$app->db->quoteValue($name);
                }
                $this->$field = implode(',', $prepare);
            }
        }
    }

    /**
     * @param ActiveQuery $query
     * @param $withoutLimit boolean
     * @return ActiveQuery|UsersQuery
     */
    public function frontSearch(ActiveQuery $query, $withoutLimit = false)
    {
        $u = Users::tableName();
        $aa = 'auth_assignment';

        if (!empty($this->role)) {
            $query->leftJoin($aa, "$u.user_id = $aa.user_id AND $aa.item_name IN ($this->role)");
            $query->andWhere("$aa.item_name IS NOT NULL");
        }

        if (!empty($this->date_from)) {
            $query->andFilterWhere(['>=', "$u.date_create", $this->date_from]);
        }

        if (!empty($this->date_to)) {
            $query->andFilterWhere(['<=', "$u.date_create", $this->date_to . ' 23:59:59']);
        }

        if (isset($this->payment_from) || isset($this->payment_to) || isset($this->payment_count_min) || isset($this->payment_count_max)) {
            $subQuery = PaymentTransactionsHistory::find()->select('user_id')->groupBy('user_id')->where(['status' => PaymentTransactionsHistory::STATUS_SUCCESS]);
            if (isset($this->payment_from)) {
                $subQuery->andFilterWhere(['>=', 'date_pay', $this->payment_from]);
            }
            if (isset($this->payment_to)) {
                $subQuery->andFilterWhere(['<=', 'date_pay', $this->payment_to . ' 23:59:59']);
            }
            if ($this->payment_count_min > 0) {
                $subQuery->andFilterHaving(['>=', 'count(user_id)', $this->payment_count_min]);
            }
            if ($this->payment_count_max > 0) {
                $subQuery->andFilterHaving(['<=', 'count(user_id)', $this->payment_count_max]);
            }

            $query->leftJoin(['h' => $subQuery], "$u.user_id = h.user_id");
            if (isset($this->payment_count_max) && $this->payment_count_max == 0 || isset($this->payment_count_min) && $this->payment_count_min == 0) {
                $query->andWhere('h.user_id IS NULL');
            } else {
                $query->andWhere('h.user_id IS NOT NULL');
            }
        }

        if (isset($this->invoice_count_min) || isset($this->invoice_count_max)) {
            $invoiceSubQuery = InvoicesUsersData::find()->select('user_id')->groupBy('user_id')->where('visible_type = 2')->notExpired();
            if ($this->invoice_count_min > 0) {
                $invoiceSubQuery->andFilterHaving(['>=', 'count(user_id)', $this->invoice_count_min]);
            }
            if ($this->invoice_count_max > 0) {
                $invoiceSubQuery->andFilterHaving(['<=', 'count(user_id)', $this->invoice_count_max]);
            }
            $query->leftJoin(['i' => $invoiceSubQuery], "$u.user_id = i.user_id");
            if (isset($this->invoice_count_max) && $this->invoice_count_max == 0 || isset($this->invoice_count_min) && $this->invoice_count_min == 0) {
                $query->andWhere('i.user_id IS NULL');
            } else {
                $query->andWhere('i.user_id IS NOT NULL');
            }
        }

        if (!empty($this->subscription)) {
            $query->andWhere("$u.subscription_status IN({$this->subscription})");
        }

        if (!empty($this->type)) {
            $this->type = explode(',', $this->type);

            if (in_array(self::TARGET_USSD_USERS, $this->type)) {
                $query->andWhere("(params->'onCreateEnv'->'prop'->'target') IS NULL");
                if (in_array(self::TARGET_CHARITY_USERS, $this->type)) {
                    $query->orWhere("$u.params->'onCreateEnv'->'prop'->'target'='\"charity\"'");
                }
            } else {
                if (in_array(self::TARGET_CHARITY_USERS, $this->type)) {
                    $query->andWhere("$u.params->'onCreateEnv'->'prop'->'target'='\"charity\"'");
                }
            }
            
            foreach ($this->type as $key => $name){
                if ($name == self::TARGET_CHARITY_USERS) {
                    unset($this->type[$key]);
                } else {
                    $this->type[$key] = \Yii::$app->db->quoteValue('"' . $name . '"');
                }
            }

            if (!empty($this->type)) {
                $type = implode(',', $this->type);
                $query->andWhere("params->'onCreateEnv'->'name' IN ($type)");
            }

        }

        $query->andFilterWhere(["$u.user_id" => $this->user_id]);
        $query->andFilterWhere(['ilike', "$u.phone", $this->phone]);

        $query->offset($this->offset);

        if (!$withoutLimit) {
            $query->limit(100);
        }
        $query->orderBy(["$u.user_id" => SORT_DESC]);

        return $query;
    }

}
