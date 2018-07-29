<?php

namespace common\models;

use \yii\db\ActiveRecord;

/**
 * This is the model class for table "categories_info".
 *
 * @property string $key
 * @property string $name
 * @property string $description
 * @property string $description_short
 * @property bool $show_main
 * @property bool $show_top
 * @property bool $is_global
 * @property bool $identifier_name
 *
 * @property Categories $category
 */
class CategoriesInfo extends ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'categories_info';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['key'], 'required'],
			[['description', 'description_short', 'key'], 'string'],
			[['c_order'], 'integer'],
			[['is_global', 'show_main', 'show_top'], 'boolean'],
			[['name', 'identifier_name'], 'string', 'max' => 255],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'key' => 'Уникальный ключ',
			'name' => 'Имя',
			'description' => 'Описание',
			'description_short' => 'Кроткое описание',
			'show_main' => 'Отображение',
			'show_top' => 'Отображение в поиске привязок',
			'is_global' => 'Показывать в начислениях',
			'identifier_name' => 'Имя идентификатора',
			'c_order' => 'Порядок',
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCategory()
	{
		return $this->hasOne(Categories::className(), ['key' => 'key']);
	}

    public function beforeSave($insert)
    {
        foreach (['name', 'description', 'description_short', 'show_main', 'show_top', 'identifier_name', 'c_order'] as $name) {
            if ((string)$this[$name] === '') {
                $this[$name] = null;
            }
        }
        return parent::beforeSave($insert);
    }


}