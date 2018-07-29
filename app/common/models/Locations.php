<?php

namespace common\models;

use Yii;
use common\components\behaviors\NestedSetsBehavior;

/**
 * This is the model class for table "cities".
 *
 * @property integer $id
 * @property string $name
 * @property integer $parent_id
 * @property integer $lft
 * @property integer $rgt
 * @property integer $level
 *
 * @method makeRoot($runValidation = true, $attributes = null)
 * @method prependTo($node, $runValidation = true, $attributes = null)
 * @method appendTo($node, $runValidation = true, $attributes = null)
 * @method insertBefore($node, $runValidation = true, $attributes = null)
 * @method insertAfter($node, $runValidation = true, $attributes = null)
 * @method deleteWithChildren()
 *
 * @method LocationsQuery parents($depth = null)
 * @method LocationsQuery children($depth = null)
 * @method LocationsQuery leaves()
 * @method LocationsQuery prev()
 * @method LocationsQuery next()
 * @method isRoot()
 * @method isChildOf($node)
 * @method isLeaf()
 */
class Locations extends \yii\db\ActiveRecord
{
	private static $location = null;
	private static $treeIds = null;

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'locations';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['name'], 'required'],
			[['parent_id', 'lft', 'rgt', 'level'], 'integer'],
			[['name'], 'string', 'max' => 255],
		];
	}

	public function behaviors()
	{
		return [
			'nestedSetsBehavior' => [
				'class' => NestedSetsBehavior::class,
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
			'name' => 'Name',
			'parent_id' => 'Parent Id',
			'lft' => 'Lft',
			'rgt' => 'Rgt',
			'level' => 'Level',
		];
	}

	public static function findById($locationId)
	{
		return static::find()->where(['id' => $locationId])->cache()->one();
	}

	public function getLocationPath()
	{
		$parents = $this->parents()->select('name')->cache()->column();
		$parents[] = $this['name'];
		return $parents;
	}

	/**@return array массив идентификаторов всех дочерних локаций + текущая + 0*/
	public static function getCurrentLocationTreeIds($location_id = null)
	{
		if (self::$treeIds === null) {
			$location = self::getCurrentLocation($location_id);
			self::$treeIds = $location->children()->select('id')->cache()->column();

			$parent = $location->parents(1)->select('id')->cache()->column();
			array_unshift(self::$treeIds, 0, isset($parent[0]) ? (int) $parent[0] : 0, $location['id']);
		}
		return self::$treeIds;
	}

	public static function getCurrentLocationIds($location_id = null)
	{
		$list = [0];

		$location = self::getCurrentLocation($location_id);
		$list[] = $location['id'];
		$parent = $location->parents(1)->select('id')->cache()->column();
		if (isset($parent[0])) {
			$list[] = $parent[0];
		}

		return $list;
	}

	public static function getLocationDefault()
	{
		return self::find()->where(['lft' => 1])->orderBy('id')->limit(1)->cache()->one();
	}

	/** Берем локацию из cookie. Если такой нет, то берем дефолтную
	 * @return array|Locations|null
	 */
	public static function getCurrentLocation($location_id = null)
	{
		if (self::$location === null) {
			if (isset($location_id)) {
				$location = self::findById($location_id);
				if (!empty($location)) {
					return self::$location = $location;
				}
			}
			self::$location = self::getUserLocation();

			if (!self::$location) {
				self::$location = self::getLocationDefault();
			}
		}
		return self::$location;
	}

	/** @todo выпелить этот антипатерн, модель ничего не должна знать о куках */
	//если true, то на сайте покажется плашка с автоматически определённым регионом
	// используется только в шаблонах
	public static function isDetectLocation()
	{
		return empty($_COOKIE['noLocationDetect']) && empty($_COOKIE['location_id']);
	}

	private static function getUserLocation()
	{
		$user = Yii::$app->user;
		if (isset($user, $user->identity)) {
			$location = self::findById($user->identity->location_id) ?: false;
		}
		if (empty($location)) {
			$locationId = !empty($_COOKIE['location_id']) ? (int) $_COOKIE['location_id'] : false;
			$location = $locationId ? self::findById($locationId) : false;
		}

		return $location;
	}

	//делает join к самому себе. Нужно что бы удобно получить области и все города этой области(все с level = 1)
	public function getCities()
	{
		return $this->hasMany(self::className(), ['tree_id' => 'id'])->andWhere(['level' => 1])->orderBy('name');
	}

	/**
	 * @inheritdoc
	 * @return LocationsQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new LocationsQuery(get_called_class());
	}
}