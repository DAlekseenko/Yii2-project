<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * @property integer $custom_category_id
 * @property integer $service_id
 *
 * @property CategoriesCustom parent
 */
class CategoryCustomServices extends ActiveRecord
{

	public function rules()
	{
		return [
			[['custom_category_id', 'service_id'], 'required'],
            [['service_id'], 'servicesIdValidator'],
            [['service_id'], 'unique', 'targetAttribute' => ['custom_category_id','service_id'], 'message' => 'Данный сервис уже добавлен!'],
			[['custom_category_id', 'service_id'], 'integer'],
		];
	}

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'service_id' => 'ID сервиса',
        ];
    }

    /**
     * Проверка на существование сервиса
     */
    public function servicesIdValidator()
    {
        if (!Services::find()->where(['id' => $this->service_id])->exists()) {
            $this->addError('service_id', 'Данный сервис не найден!');
        }
    }


	public function getParent()
	{
		return $this->hasOne(CategoriesCustom::className(), ['id' => 'custom_category_id']);
	}

	public static function getLink($categoryId, $serviceId)
	{
		return self::find()->where(['custom_category_id' => $categoryId, 'service_id' => $serviceId])->one();
	}
}
