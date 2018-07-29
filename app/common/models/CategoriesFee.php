<?php
namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "categories_fee".
 *
 * @property integer $id
 * @property integer $category_id
 * @property string $name
 * @property string $value
 * @property string $date_start
 * @property string $date_end
 *
 * @property Categories $category
 */
class CategoriesFee extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'categories_fee';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['category_id', 'value'], 'required'],
			[['category_id'], 'integer'],
			[['value'], 'number'],
			[['name'], 'string', 'max' => 256],
			[['name'], 'default', 'value' => null],
			[['date_start'], 'default', 'value' => '01.02.1970'],
			[['date_end'], 'default', 'value' => '01.02.2100'],
		];
	}

	public function afterFind()
	{
		$this->date_start = date('d.m.Y H:i', strtotime($this->date_start));
		$this->date_end = date('d.m.Y H:i', strtotime($this->date_end));
		parent::afterFind();
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'id',
			'category_id' => 'Категория',
			'name' => 'Имя',
			'value' => 'Значение',
			'date_start' => 'Дата начала',
			'date_end' => 'Дата окончания',
		];
	}

	/**Список всех категорий, на которые можно указать комиссию
	 * @return array
	 */
	public function getListCategories()
	{
		$categories = Categories::find()->roots()->orderBy('name')->allWithInfo();
		return ArrayHelper::map($categories, 'id', 'name');
	}

	public function getFeeName()
	{
		return $this['name'] ? $this['name'] : $this['category']['name'];
	}

	public function beforeSave($insert)
	{
		if ($this->date_start) {
			$this->date_start = date('Y-m-d H:i:s', strtotime($this->date_start));
		}
		if ($this->date_end) {
			$this->date_end = date('Y-m-d H:i:s', strtotime($this->date_end));
		}
		return parent::beforeSave($insert);
	}

	public static function findByCategoryId($categoryId)
	{
		return self::find()->byCategoryId($categoryId)->active()->cache()->one();
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCategory()
	{
		return $this->hasOne(Categories::className(), ['id' => 'category_id']);
	}

	/**
	 * @inheritdoc
	 * @return CategoriesFeeQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new CategoriesFeeQuery(get_called_class());
	}
}