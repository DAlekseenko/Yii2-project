<?php

namespace common\models;

/**
 * @property string $plug
 * @property string $code
 * @property string $title
 * @property int $service_id
 * @property float $sum
 * @property string $success_sms_text
 * @property array $fields
 * @property float $total
 * @property string $subscribe_start_sms
 * @property string $subscribe_end_sms
 * @property bool $subs_enable
 * @property string uuid
 */
class Ussd extends AbstractModel implements \JsonSerializable
{
    const PLUG_NAME_CHARITY_USSD_222 = 'charity_ussd';
    const PLUG_NAME_CHARITY_USSD_223 = 'charity_ussd_223';
    const PLUG_NAME_CHARITY_SMS_2121 = 'charity_sms_2121';

    const PLUG_NAMES = [
        self::PLUG_NAME_CHARITY_USSD_222 => 'USSD *222#',
        self::PLUG_NAME_CHARITY_USSD_223 => 'USSD *223#',
        self::PLUG_NAME_CHARITY_SMS_2121 => 'SMS 2121',
    ];

    public $fieldsAsText;

    public static function tableName()
    {
        return 'ussd';
    }

    public function rules()
    {
        return [
            [['plug', 'code', 'title', 'service_id', 'sum', 'success_sms_text'], 'required'],
            [['plug', 'code'], 'unique', 'targetAttribute' => ['plug', 'code'], 'message' => 'Данная связка уже существует!'],
            ['service_id', 'integer'],
            [['service_id'], 'serviceIdValidator'],
            ['sum', 'number'],
            [['title'], 'string', 'max' => 20],
            [['code', 'subscribe_start_sms', 'subscribe_end_sms'], 'string'],
            [['subs_enable'], 'boolean'],
        ];
    }

    /**
     * Проверка на существование сервиса
     */
    public function serviceIdValidator()
    {
        if (!Services::find()->where(['id' => $this->service_id])->exists()) {
            $this->addError('service_id', 'Данный сервис не найден!');
        }
    }

    public function afterFind()
    {
        $this->fieldsAsText = is_array($this->fields) ? implode("\n", $this->fields) : $this->fields;
        parent::afterFind();
    }

    public function load($data, $formName = null)
    {
        if (!empty($data['Ussd']['fieldsAsText'])) {
            $this->fields = array_map('trim', preg_split('/\n+/', $data['Ussd']['fieldsAsText']));
        } else {
            $this->fields = null;
        }
        return parent::load($data, $formName);
    }

    public function afterSave($insert, $changedAttributes)
    {
        $this->fieldsAsText = is_array($this->fields) ? implode("\n", $this->fields) : $this->fields;
        return parent::afterSave($insert, $changedAttributes);
    }

    /** Обновляет сумму собранных средств по благотворительности. */
    public static function updateTotalSum()
    {
        $pdo = \yii::$app->db->getMasterPdo();

        $u = self::tableName();
        $h = PaymentTransactionsHistory::tableName();
        $sql = "
			UPDATE $u SET total = (select sum($h.sum) from $h where $h.service_id = $u.service_id and $h.fields->0->'value' = $u.fields->0  )
		";

        return $pdo->exec($sql);
    }

    public function jsonSerialize()
    {
        return array_merge($this->getAttributes(), ['fieldsAsText' => $this->fieldsAsText]);
    }
}
