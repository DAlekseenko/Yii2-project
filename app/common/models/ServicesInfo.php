<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "services_info".
 *
 * @property integer $service_id
 * @property string $name
 * @property string $description
 * @property string $description_short
 * @property array  $fields
 * @property bool $show_main
 * @property bool $show_top
 * @property integer $success_counter
 * @property bool $forbidden_for_guest
 * @property string $first_field_name
 *
 * @property Services 	$service
 * @property Masks 		$mask
 */
class ServicesInfo extends AbstractModel
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'services_info';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['service_id'], 'required'],
			[['service_id'], 'integer'],
			[['description', 'description_short', 'tags', 'first_field_name', 'mask_name'], 'string'],
			[['name'], 'string', 'max' => 255],
			[['show_main', 'show_top', 'forbidden_for_guest'], 'boolean'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'service_id' => 'Service ID',
			'name' => 'Услуга',
			'description' => 'Описание',
			'description_short' => 'Короткое описание',
			'show_main' => 'Отображение',
			'show_top' => 'Отображение в поиске привязок',
			'fields' => 'Fields',
			'tags' => 'Теги для поиска',
			'forbidden_for_guest' => 'Запрещена для гостей',
			'first_field_name' => 'Название первого поля',
            'mask_name' => 'Название маски',
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getService()
	{
		return $this->hasOne(Services::className(), ['id' => 'service_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getMask()
	{
		return $this->hasOne(Masks::className(), ['name' => 'mask_name']);
	}

	public function setFields($fields)
	{
		if (!is_array($fields)) {
			$fields = json_decode($fields, 1);
		}
		if (isset($fields)) {
			foreach ($fields as &$field) {
				unset($field['value']);
			}
			$this->fields = $fields;
		}

		return $this;
	}

	public function getIdentifierName()
	{
		return isset($this->fields[0]['name']) ? $this->fields[0]['name'] : null;
	}

    public function beforeSave($insert)
    {
        foreach (['name', 'description', 'description_short', 'show_main', 'show_top'] as $name) {
            if ((string)$this[$name] === '') {
                $this[$name] = null;
            }
        }
        return parent::beforeSave($insert);
    }

}
