<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "payment_favorites".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $service_id
 * @property array $fields
 * @property string $name
 * @property integer $transaction_id
 *
 * @property Users $user
 * @property Services $service
 */
class PaymentFavorites extends AbstractModel
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'payment_favorites';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['user_id', 'service_id', 'name'], 'required'],
			[['id', 'user_id', 'service_id', 'transaction_id'], 'integer'],
			[['name'], 'string', 'max' => 32]
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'user_id' => 'User ID',
			'service_id' => 'Service ID',
			'fields' => 'Fields',
			'name' => 'Название',
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getUser()
	{
		return $this->hasOne(Users::className(), ['user_id' => 'user_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getService()
	{
		return $this->hasOne(Services::className(), ['id' => 'service_id'])->with('servicesInfo');
	}

	/**
	 * @inheritdoc
	 * @return PaymentFavoritesQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new PaymentFavoritesQuery(get_called_class());
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
}
