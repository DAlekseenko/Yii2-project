<?php

namespace common\models;

use common\components\services\Environment;
use common\components\services\Helper;
use common\models\validators\PaymentTransactionsSumValidator;
use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\validators\Validator;

/**
 * This is the model class for table "payment_transactions".
 *
 * @property int $id
 * @property string $uuid
 * @property integer $user_id
 * @property integer $service_id
 * @property string $status
 * @property string $date_create
 * @property string $date_pay
 * @property string $method
 * @property float $sum
 * @property array $fields
 * @property array $erip_data
 * @property integer $erip_payment_id
 * @property integer $is_in_mts_register
 * @property integer $bgate_order_id
 * @property integer $mts_register_status
 * @property integer $status_mc
 * @property integer $verify_status
 * @property integer $cancel_status
 * @property array $transaction_params
 * @property string $bank_date_create
 * @property string $date_create_mts
 *
 * @property Services $service
 * @property Invoices $invoice
 * @property Users $user
 */
class PaymentTransactions extends AbstractModel implements \JsonSerializable
{
    const STATUS_NEW = 'NEW';
    const STATUS_IN_PROCESS = 'IN_PROCESS';
    const STATUS_SUCCESS = 'SUCCESS';
    const STATUS_FAIL = 'FAIL';
    const STATUS_REVERSAL = 'REVERSAL';

    const METHOD_PHONE = 'PHONE';

    const DEFAULT_CURRENCY = GLOBAL_CURRENCY;

    const VERIFY_STATUS_NOT_VERIFIED = 0;
    const VERIFY_STATUS_SUCCESS = 1;
    const VERIFY_STATUS_FAIL = -1;

    const CANCEL_STATUS_NOT_CANCELED = 0;
    const CANCEL_STATUS_SUCCESS = 1;
    const CANCEL_STATUS_FAIL = -1;

    const MTS_REGISTRY_STATUS_OK = 0;
    const MTS_REGISTRY_STATUS_NO_IN_PC = 1;
    const MTS_REGISTRY_STATUS_NO_IN_MTS = 2;
    const MTS_REGISTRY_STATUS_NO_ANYWHERE = 3;

    const PARAMS_SUCCESS_MSG = 'successMsg';
    const PARAMS_ON_PAY_ENVIRONMENT = 'onPayEnv';
    const DISABLE_SUCCESS_SMS = 'disableSuccessSms';
    const PAYMENT_CANCEL_REASON = 'cancelReason';
    const PARAM_UNSUBSCRIPTION_MODE = 'unsubscriptionMode';

    const SERIALIZE_MODE_VERIFY = 1;
    const SERIALIZE_MODE_CHECK_REGISTRY = 2;
    const SERIALIZE_MODE_FRONT_END = 3;

    /** percent  1.32 MTS + 0.25 bank */
    const TRANSACTION_COST = 1.57;

    public static $serializeMode = self::SERIALIZE_MODE_VERIFY;

    public static $statusesName = [
        self::STATUS_NEW => 'Новый',
        self::STATUS_IN_PROCESS => 'В процессе',
        self::STATUS_SUCCESS => 'Оплачено',
        self::STATUS_FAIL => 'Не выполнено',
        self::STATUS_REVERSAL => 'Отменено',
    ];

    public static $verifyStatusesNames = [
        self::VERIFY_STATUS_NOT_VERIFIED => 'Не верифицировалось',
        self::VERIFY_STATUS_SUCCESS => "ОК",
        self::VERIFY_STATUS_FAIL => "Ошибка"
    ];

    public static $cancelStatusesNames = [
        self::CANCEL_STATUS_NOT_CANCELED => 'Не отменялось',
        self::CANCEL_STATUS_SUCCESS => "ОК",
        self::CANCEL_STATUS_FAIL => "Ошибка"
    ];

    public static $mtsRegistryStatuses = [
        self::MTS_REGISTRY_STATUS_OK => "Совпадает ПЦ и МТС",
        self::MTS_REGISTRY_STATUS_NO_IN_PC => "Есть в МТС, нет в ПЦ",
        self::MTS_REGISTRY_STATUS_NO_IN_MTS => "Есть в ПЦ, нет в МТС",
        self::MTS_REGISTRY_STATUS_NO_ANYWHERE => "Нет ни в ПЦ, ни в МТС"
    ];

    /** @var null|string иногда происходит join с другими таблицами и транзакции присваевается имя в это поле */
    public $item_name = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment_transactions';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uuid', 'status', 'method'], 'string'],
            [['user_id', 'service_id', 'erip_payment_id'], 'integer'],
            [['date_create', 'date_pay'], 'safe'],
            [['sum'], 'number'],
            [['sum'], PaymentTransactionsSumValidator::class],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'uuid' => 'uuid',
            'user_id' => 'User id',
            'service_id' => 'ID сервиса ЕРИП',
            'status' => 'Результат',
            'date_create' => 'Дата и время',
            'date_pay' => 'Дата оплаты',
            'method' => 'Method',
            'sum' => 'Сумма',
            'fields' => 'Fields',
            'bgate_order_id' => 'Bgate ID',
            'date_create_mts' => 'Дата и время списания с МТС',
            'status_mc' => 'Статус списания с МТС',
            'erip_payment_id' => 'ЕРИП ID',
            'is_in_mts_register' => 'В МТС',
            'verify_status' => 'Статус верификации',
            'cancel_status' => 'Статус отмены в банке',
            'mts_register_status' => 'Статус добавления транзакции в результат с МТС',
            'is_reversal' => 'Статус сторнирования в банке',
        ];
    }

    public function getFieldValue($fieldKey)
    {
        foreach ($this->fields ?: [] as $field) {
            if ($field['name'] == $fieldKey) {
                return $field['value'];
            }
        }
        return null;
    }

    public function fieldToExport($count = null)
    {
        $line = '';
        foreach ($this->fields ?: [] as $index => $field) {
            if (isset($field['name'], $field['value'])) {
                $line .= "{$field['name']}:{$field['value']}; ";
            }
            if (isset($count) && $index == $count - 1) {
                break;
            }
        }
        return $line;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Services::className(), ['id' => 'service_id'])->with('servicesInfo');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFavorite()
    {
        return $this->hasOne(PaymentFavorites::className(), ['transaction_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInvoice()
    {
        return $this->hasOne(Invoices::className(), ['uuid' => 'uuid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['user_id' => 'user_id']);
    }

    /**
     * @inheritdoc
     * @return PaymentTransactionsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new PaymentTransactionsQuery(get_called_class());
    }

    public function getStatusName()
    {
        return self::$statusesName[$this->status];
    }

    /**
     * Возвращает сумму без комиссии
     * @return int
     */
    public function getSum()
    {
        return $this->getEripDataArray('sum.userValue', 0);
    }

    public function getCommission()
    {
        return $this->getEripDataArray('sum.commission', 0);
    }

    public function getCurrency()
    {
        return $this->getEripDataArray('sum.currency') ?: self::DEFAULT_CURRENCY;
    }

    public function getPaymentId()
    {
        return $this->erip_payment_id ?: $this->getEripDataArray('paymentInfo.paymentId', null);
    }

    public function getEripDataArray($key = null, $default = null)
    {
        $value = $key ? ArrayHelper::getValue($this->erip_data, $key, $default) : $this->erip_data;
        /** @todo костыль для моб. приложения, выпелить после релиза iOS */
        if (isset($value['sum']) && !isset($value['sum']['min'])) {
			$value['sum']['min'] = 0;
		}
		return $value;
    }

    public function getCustomSuccessMsg()
    {
        return isset($this->transaction_params[self::PARAMS_SUCCESS_MSG]) ? $this->transaction_params[self::PARAMS_SUCCESS_MSG] : null;
    }

    public function disableSuccessSms()
    {
        $params = $this->transaction_params;
        $params[self::DISABLE_SUCCESS_SMS] = true;
        $this->transaction_params = $params;
    }

    public function canSendSuccessSms()
    {
        return !(isset($this->transaction_params[self::DISABLE_SUCCESS_SMS]) && $this->transaction_params[self::DISABLE_SUCCESS_SMS] === true);
    }

    public function setCustomSuccessMsg($value, $smsProduct = null)
    {
        $params = $this->transaction_params;
        $params[self::PARAMS_SUCCESS_MSG] = ['text' => $value, 'product' => $smsProduct];
        $this->transaction_params = $params;

        return $this;
    }

    public function setUnsubscriptionMode($mode = true)
    {

        $params = $this->transaction_params;
        $params[self::PARAM_UNSUBSCRIPTION_MODE] = $mode;
        $this->transaction_params = $params;

        return $this;
    }

    public function isSubscriptionNeeded()
    {
        return !(isset($this->transaction_params[self::PARAM_UNSUBSCRIPTION_MODE]) && $this->transaction_params[self::PARAM_UNSUBSCRIPTION_MODE] === true);
    }

    /**
     * @param  integer $code
     * @param  string $description
     * @return $this
     */
    public function setCancelReason($code, $description)
    {
        $params = $this->transaction_params;
        $params[self::PAYMENT_CANCEL_REASON] = ['code' => $code, 'text' => $description];
        $this->transaction_params = $params;

        return $this;
    }

    public function setOnPayEnvironment(Environment $env, $overwrite = true)
    {
        if ($overwrite === false && isset($this->transaction_params[self::PARAMS_ON_PAY_ENVIRONMENT])) {
            return $this;
        }
        $params = $this->transaction_params;
        $params[self::PARAMS_ON_PAY_ENVIRONMENT] = $env;
        $this->transaction_params = $params;

        return $this;
    }

    /**
     * Возвращает список полей карты
     *
     * @return array
     */
    public function getFieldsMap()
    {
        return $this->fields ?: [];
    }

	/**
	 * @return float
	 * @throws \Exception
	 */
    public function getMaxSum()
    {
		/** @var \api\components\services\ParamsService\ParamsService $paramsService */
		$paramsService = \yii::$app->{SERVICE_PARAMS};

    	return $paramsService->getOperationLimit();
    }

    public function getTransactionKey()
    {
        return $this->uuid . '-' . $this->service_id;
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->uuid = $this->uuid ?: Helper::createUuid();
        }
        return parent::beforeSave($insert);
    }

    /**
     * Возвращает истину если по платежу могут быть начисления.
     */
    public function hasInvoice()
    {
        return $this->status == self::STATUS_SUCCESS && count($this->getFieldsMap()) == 1;
    }

    public function setNewTransaction($serviceId, array $eripSum, $userId = null)
    {
        $this->status = self::STATUS_NEW;
        $this->service_id = $serviceId;
        if (isset($userId)) {
            $this->user_id = $userId;
        } else {
            $this->user_id = Yii::$app->user->isGuest ? null : Yii::$app->user->id;
        }
        $this->erip_data = ['sum' => $eripSum];
    }

    public function updateNewTransaction($sum, $commission, $bankDateCreate, $paymentInfo, $fields = null)
    {
        $this->sum = $sum + $commission;

        $eripData = $this->erip_data ?: [];
        if (isset($eripData['sum'])) {
            $eripData['sum']['userValue'] = $sum;
            $eripData['sum']['commission'] = $commission;
        }
        $eripData['paymentInfo'] = $paymentInfo;
        $this->erip_data = $eripData;

        if (isset($bankDateCreate)) {
            $this->bank_date_create = $bankDateCreate;
        }
        $this->fields = $fields;
        $this->erip_payment_id = $this->getPaymentId();
    }

    public function setFinalStatus($oid, $success = true)
    {
        $this->status = $success ? $this::STATUS_SUCCESS : $this::STATUS_FAIL;
        $this->bgate_order_id = $oid;

        return $this;
    }

    public function sumValidate()
    {
        $allowedSum = $this->getMaxSum();
        if (isset($allowedSum) && $allowedSum < $this->sum) {
            $this->addError('sum', 'Сумма платежа не может превышать ' . $allowedSum . ' руб');
            return false;
        }
        return true;
    }

    public function updateNewTransactionOnConfirm($userId = null)
    {
        if (isset($userId)) {
            $this->user_id = $userId;
        }
        $this->status = PaymentTransactions::STATUS_IN_PROCESS;
        $this->date_create = date('Y-m-d H:i:s', time());
    }

    public function isVerifyPossible()
    {
        return ($this->status == self::STATUS_SUCCESS && $this->is_in_mts_register);

    }

    public function isReversed()
    {
        return ($this->status == self::STATUS_REVERSAL);

    }

    public function getVerifyStatusText()
    {
        return isset(self::$verifyStatusesNames[$this->verify_status]) ? self::$verifyStatusesNames[$this->verify_status] : '';
    }

    public function getCancelStatusText()
    {
        return isset(self::$cancelStatusesNames[$this->cancel_status]) ? self::$cancelStatusesNames[$this->cancel_status] : '';
    }

    public function getMtsRegisterStatusText()
    {
        return isset(self::$mtsRegistryStatuses[$this->mts_register_status]) ? self::$mtsRegistryStatuses[$this->mts_register_status] : null;
    }

    public function getRes()
    {
        return $this->sum * $this->service->provider_fee / 100;
    }

    public function getTax()
    {
        return $this->sum * $this::TRANSACTION_COST / 100;
    }

    public function getIncome()
    {
        return $this->getRes() - $this->getTax();
    }


    public function getListFrontEndAttributes()
    {
        $atr = [
            'res' => number_format($this->getRes(), 10, '.', ''),
            'tax' => $this->getTax(),
            'income' => $this->status == $this::STATUS_SUCCESS ? number_format($this->getIncome(), 10, '.', '') : '',
            'invoice' => $this->invoice,
            'phone' => $this->user->phone,
            'fio' => $this->user->getUserFIO(),
            'service_name' => $this->service->name,
            'first_field' => $this->fieldToExport(1),
            'status' => strtolower($this->status),
            'sum' => number_format($this->sum, 2, '.', ''),
            'provider_fee' => number_format($this->service->provider_fee, 3, '.', ''),
            'transaction_params' => $this->transaction_params
        ];
        return array_merge($this->getAttributes(), $atr);
    }


    public function jsonSerialize()
    {
        switch (self::$serializeMode) {
            case self::SERIALIZE_MODE_CHECK_REGISTRY:
                return $this->getAttributes(['uuid', 'sum', 'date_pay']);
            case self::SERIALIZE_MODE_FRONT_END:
                return $this->getListFrontEndAttributes();
            case self::SERIALIZE_MODE_VERIFY:
            default:
                return $this->getAttributes(['uuid', 'erip_payment_id', 'sum', 'bank_date_create']);
        }
    }
}