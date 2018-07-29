<?php

namespace common\models;

use common\components\behaviors\ImgBehavior;
use \yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "services".
 *
 * @property integer $id
 * @property integer $name
 * @property integer $category_id
 * @property integer $location_id
 * @property integer $provider_fee
 * @property integer $date_removed
 *
 * @mixin ImgBehavior
 *
 * @property ServicesInfo $servicesInfo
 * @property Locations $location
 * @property Categories $category
 * @property ServicesLists|array $lists
 */
class Services extends ActiveRecord
{
	const SORT_MODE_DEFAULT = 1; //локация, популярность, имя
	const SORT_MODE_POPULAR = 2; //популярность, имя

	public $info_name = null;

	public $info_description = null;

	public $info_description_short = null;

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'services';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['id', 'name', 'category_id', 'location_id'], 'required'],
			[['id', 'category_id'], 'integer'],
			[['name'], 'string', 'max' => 255],
			['date_removed', 'safe'],
		];
	}

	public function behaviors()
	{
		return [
			'imgBehavior' => [
				'class' => ImgBehavior::class,
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'name' => 'Услуга',
			'category_id' => 'Категория',
			'location_id' => 'Локация',
		];
	}

	public function getUkey()
	{
		return $this->id;
	}

	public function getClientFeeValue()
	{
		$category = Categories::findById($this->category_id);
		$fee = CategoriesFee::findByCategoryId($category->tree_id);
		return $fee ? $fee['value'] : false;
	}

	/**
	 * @param int  $serviceId
	 * @param bool $withInfo
	 * @return self
	 */
	public static function findById($serviceId, $withInfo = false)
	{
		$query = static::find();

		if ($withInfo) {
			$query->with('servicesInfo');
		}

		return $query->where(['id' => $serviceId])->cache()->one();
	}

	public function getIdentifierName()
	{
		return $this->servicesInfo ? ($this->servicesInfo->first_field_name ?: $this->servicesInfo->getIdentifierName()) : null;
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getServicesInfo()
	{
		return $this->hasOne(ServicesInfo::className(), ['service_id' => 'id']);
	}

	/**
	 * @return \common\models\CategoriesQuery
	 */
	public function getCategory()
	{
		return $this->hasOne(Categories::className(), ['id' => 'category_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getLocation()
	{
		return $this->hasOne(Locations::className(), ['id' => 'location_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getLists()
	{
		return $this->hasMany(ServicesLists::className(), ['service_id' => 'id']);
	}

	/**
	 * Проверяет, находится ли сервис в каком-либо списке.
	 *
	 * @param  array $listNames
	 * @return bool
	 */
	public function isInList(array $listNames)
	{
		$lists = ArrayHelper::getColumn($this->lists ?: [], 'list_name', []);
		return !empty(array_intersect($listNames, $lists));
	}

	/**
	 * @inheritdoc
	 * @return ServicesQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new ServicesQuery(get_called_class());
	}

	public function __get($name)
	{
		if (in_array($name, array_keys(ServicesInfo::getTableSchema()->columns))) {
			if ($this->isRelationPopulated('servicesInfo') && isset($this->servicesInfo[$name])) {
				return $this->servicesInfo[$name];
			}
			if (isset($this->{'info_' . $name})) {
				return $this->{'info_' . $name};
			}
			if (!$this->hasAttribute($name)) {
				return null;
			}
		}
		return parent::__get($name);
	}
}