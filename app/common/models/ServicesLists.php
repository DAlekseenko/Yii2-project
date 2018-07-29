<?php

namespace common\models;

/**
 * @property string $list_name
 * @property integer $service_id
 * @property array $params
 *
 * @property Services $service
 */
class ServicesLists extends AbstractModel implements \JsonSerializable
{
    const LIST_WITH_EMPTY_INVOICES = 'invoice_empty';
    const LIST_WITH_ONE_OFF_INVOICE = 'one_off_invoice';
    const LIST_ACCEPT_SERVICES = 'list_accept';

	public static $listLabels = [
		self::LIST_WITH_ONE_OFF_INVOICE => 'Одноразовые начисления',
		self::LIST_WITH_EMPTY_INVOICES => 'Пустые начисления',
        self::LIST_ACCEPT_SERVICES => 'Сервисы с акцептом'
	];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'services_lists';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['list_name'], 'string'],
            [['service_id'], 'required'],
            [['service_id'], 'serviceIdValidator'],
            [['service_id'], 'unique', 'targetAttribute' => ['service_id', 'list_name'], 'message' => 'Данная связка уже существует!']
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

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'list_name' => 'Название списка',
            'service_id' => 'Service_id',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Services::className(), ['id' => 'service_id']);
    }

    public function jsonSerialize()
    {
        return [
            'list_name' => $this->list_name,
            'service_id' => $this->service_id,
            'service_name' => $this->service->name,
            'params'=> $this->params
        ];
    }
}
