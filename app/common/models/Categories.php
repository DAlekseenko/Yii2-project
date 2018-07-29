<?php

namespace common\models;

use common\models\CategoriesInfo;
use common\models\sql\CategoriesSearchSql;
use frontend\models\InvoicesIgnoreDefault;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use common\components\behaviors\NestedSetsBehavior;
use common\components\behaviors\ImgBehavior;

/**
 * This is the model class for table "categories".
 *
 * @property integer $id
 * @property string  $name
 * @property integer $parent_id
 * @property integer $lft
 * @property integer $rgt
 * @property integer $level
 * @property integer $tree_id
 * @property integer $location_id
 * @property integer $date_removed
 * @property string  $key
 *
 * @method makeRoot($runValidation = true, $attributes = null)
 * @method prependTo($node, $runValidation = true, $attributes = null)
 * @method appendTo($node, $runValidation = true, $attributes = null)
 * @method insertBefore($node, $runValidation = true, $attributes = null)
 * @method insertAfter($node, $runValidation = true, $attributes = null)
 * @method deleteWithChildren()
 *
 * @method CategoriesQuery parents($depth = null)
 * @method CategoriesQuery children($depth = null)
 * @method CategoriesQuery leaves()
 * @method CategoriesQuery prev()
 * @method CategoriesQuery next()
 * @method isRoot()
 * @method isChildOf($node)
 * @method isLeaf()
 *
 * @mixin ImgBehavior
 *
 * @property Locations $location
 * @property CategoriesInfo $categoriesInfo
 * @property InvoicesIgnoreDefault $ignored
 * @property Services[] $services
 * @property ServicesCount $servicesCount
 */
class Categories extends \yii\db\ActiveRecord
{
	const FIELD_SHOW_MAIN = 'show_main';
	const FIELD_SHOW_TOP = 'show_top';

	const SORT_MODE_ALPHABET = 'alphabet';
	const SORT_MODE_USER = 'user';

	/** @var null|int переименовать когда удалим отношение services_count */
	public $servicesCountHash = null;

	public $info_name = null;

	public $info_description = null;

	public $info_description_short = null;

	public $info_show_main = null;

	public $info_is_global = null;

	public $info_identifier_name = null;

	public $info_show_top = null;

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'categories';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['id', 'name'], 'required'],
			[['id', 'parent_id', 'lft', 'rgt', 'level', 'tree_id'], 'integer'],
			[['name'], 'string', 'max' => 255],
			['date_removed', 'safe'],
		];
	}

	public function behaviors()
	{
		return [
			'nestedSetsBehavior' => [
				'class' => NestedSetsBehavior::class,
			],
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
			'id' => 'Идентификатор',
			'name' => 'Имя',
			'parent_id' => 'Родительская категория',
			'lft' => 'Lft',
			'rgt' => 'Rgt',
			'level' => 'Level',
			'tree_id' => 'Корневая категория',
		];
	}

	/**
	 * @param $key
	 * @return array|Categories|null
	 */
	public static function findByUkey($key)
	{
		return self::find()->where(['key' => $key])->one();
	}

	public function getUkey()
	{
		return $this->key;
	}

	public function getBreadcrumbs()
	{
		$result = [];
		foreach ($this->getParents(true) as $node) {
			$result[] = ['url' => Url::to(['/categories', 'id' => $node['id']]), 'class' => '--breadcrumb-item', 'label' => $node['name'], 'data-id' => $node['id']];
		}
		return $result;
	}

	public function getCategoryNamePath($addCurrent = false)
	{
		return ArrayHelper::getColumn($this->getParents($addCurrent), 'name');
	}

	/**Получить цепочку родителей. В начале идёт корневая, потом по порядку вниз дерева
	 * @param bool $addCurrent
	 * @return array|Categories[]|\yii\db\ActiveRecord[]
	 */
	public function getParents($addCurrent = false)
	{
		$parents = $this->parents()->with('categoriesInfo')->andWhere('date_removed IS NULL')->cache()->all();
		if ($addCurrent) {
			$parents[] = $this;
		}
		return $parents;
	}

	//берем 3 дочерние категории текущего региона(где $field = true). Если 3х нет, то добираем из услуг(где $field =  true), потом снова добираем из категорий, потом снова из услуг без $field
	//field - по какому полу отображать. Это поле должно быть и в Categories_info и в Services_info
	public function getChildrenItems($field = Categories::FIELD_SHOW_MAIN)
	{
		$categories = $this->children()->joinWith('categoriesInfo')->byShowField($field)->innerJoinWithServicesCount()->limit(3)->all();

		$result = [];
		foreach ($categories as $item) {
			if ($item['categoriesInfo'][$field]) {
				$result[] = $item;
			}
		}

		if (count($result) == 3) {
			return $result;
		}

		$services = $this->getAllServices()->joinWith('servicesInfo')->byShowField($field)->limit(3 - count($result))->all();
		foreach ($services as $service) {
			if ($service['servicesInfo'][$field]) {
				$result[] = $service;
			}
		}

		foreach ($categories as $item) {
			if (!$item['categoriesInfo'][$field] && count($result) < 3) {
				$result[] = $item;
			}
		}

		foreach ($services as $service) {
			if (!$service['servicesInfo'][$field] && count($result) < 3) {
				$result[] = $service;
			}
		}

		usort($result, function ($item) {
			return $item instanceof Categories ? -1 : 1;
		});

		return $result;
	}

	public function getAllServiceIds($onlyForCurrentLocation = true)
	{
		return $this->getAllServices($onlyForCurrentLocation)->cache()->column();
	}

	/** Полуйчаем все услуги у текущей категории + всех дочерних категорий
	 * @return ServicesQuery
	 */
	public function getAllServices($onlyForCurrentLocation = true)
	{
		$condition = ['in', Services::tableName() . '.category_id', $this->getTreeIds()];
		$query = Services::find();
		if ($onlyForCurrentLocation === true) {
			$query->currentLocation();
		}
		return $query->with('servicesInfo')->andWhere($condition)->andWhere(Services::tableName() . '.date_removed IS NULL');
	}

	//получить глобальную категорию по какому-либо categoryId
	public static function getGlobalByCategoryId($categoryId)
	{
		//ищем глобальную среди родителей. Если не нашли, то берем самую родительскую
		$parents = self::findById($categoryId)->getParents(true);
		foreach (array_reverse($parents) as $parent) {
			if ($parent['is_global']) {
				return $parent;
			}
		}
		return reset($parents);
	}

	public static function getClosestCategories($parentId, $sortMode = self::SORT_MODE_ALPHABET, $location_id = null)
	{
		$locationIds = Locations::getCurrentLocationIds($location_id);
		$key = 'closestCategories_' . implode('-', $locationIds) . '_' . (int) $parentId . '_' . $sortMode;
		$params = Yii::$app->cache->get($key);
		if ($params === false) {
			$category = $parentId < 10000000000 ? CategoriesCustom::find()->where(['id' => $parentId])->withServices()->one() : Categories::find()->byId($parentId)->withServices()->with('categoriesInfo')->one();
			if (empty($category)) {
				throw new \yii\web\NotFoundHttpException('Category not found');
			}
			$params = ['category' => $category,
					'closestChildren' => $category->getClosest($sortMode)
			];
			Yii::$app->cache->setWithDependency($key, $params, [Categories::tableName(), Services::tableName()]);
		}

		return $params;
	}

	/** @todo нуждается в рефакторинге, вынести в CategoriesSearchSql */
	public function getClosest($sortMode = self::SORT_MODE_ALPHABET)
	{
		$pdo = yii::$app->db->getMasterPdo();
		$id = $this->id;
		$level = $this->level;
		$constrains = self::getCategoryConstrains(Locations::getCurrentLocationIds(), (int)$id, $level);
		$c = Categories::tableName();
		$ci = CategoriesInfo::tableName();

		$order = "$c.name";
		if ($sortMode == self::SORT_MODE_USER) {
			$order = "coalesce($ci.c_order, 0) DESC, " . $order;
		}

		$query = "
				SELECT $c.*, $ci.name AS info_name, $ci.description AS info_description, $ci.description_short AS info_description_short,
					count(s.id) AS services_count
				FROM $c
				LEFT JOIN $ci USING(key)
				LEFT JOIN (
					SELECT ss.*, ss.path[$level+2] as parent FROM services AS ss WHERE $constrains
				) AS s ON $c.id = s.parent AND s.date_removed IS NULL
				WHERE $c.parent_id = $id
				GROUP BY $c.id, $ci.key
				HAVING count(s.id) > 0
				ORDER BY $order;";

		return $pdo->query($query)->fetchAll($pdo::FETCH_CLASS, self::className());
	}

	/** @todo нуждается в рефакторинге, вынести в CategoriesSearchSql */
	public static function getCategoryConstrains(array $locations, $id, $level)
	{
		$items = [" ss.meta->'glob'->($level) = '$id' "];
		if (count($locations) == 2) {
			$items[] = " ss.meta->'reg$locations[1]'->($level) = '$id' ";
			$items[] = " ss.meta->'loc$locations[1]'->($level) = '$id' ";
		} elseif (count($locations) == 3) {
			$items[] = " ss.meta->'loc$locations[1]'->($level) = '$id' ";
			$items[] = " ss.meta->'loc$locations[2]'->($level) = '$id' ";
		}
		return ' ( ' . implode(' OR ', $items) . ' ) ';
	}

	//массив идентификаторов: id текущей категории + ids всех дочерних
	public function getTreeIds()
	{
		$ids = [$this->getPrimaryKey()];
		return array_merge($ids, $this->children()->select('id')->cache()->column());
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getServicesCount()
	{
		return $this->hasOne(ServicesCount::className(), ['category_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCategoriesInfo()
	{
		return $this->hasOne(CategoriesInfo::className(), ['key' => 'key']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getServices()
	{
		return $this->hasMany(Services::className(), ['category_id' => 'id'])->with('servicesInfo');
	}

	/** @todo нуждается в рефакторинге, вынести в CategoriesSearchSql */
	public static function findGlobalByShow($field = Categories::FIELD_SHOW_MAIN)
	{
		$pdo = yii::$app->db->getMasterPdo();
		$pdo->setAttribute($pdo::ATTR_EMULATE_PREPARES, true);
		$constrains = self::getGlobalCategoryConstrains(Locations::getCurrentLocationIds());

		$c = Categories::tableName();
		$ci = CategoriesInfo::tableName();

		$query = "
			SELECT $c.*, $ci.name AS info_name, $ci.description AS info_description, $ci.description_short AS info_description_short,
				count(s.id) AS services_count
			FROM $c
			LEFT JOIN $ci USING(key)

			LEFT JOIN  (
				SELECT ss.*, ss.path[1] AS parent FROM services AS ss WHERE $constrains AND ss.date_removed IS NULL
			) s ON $c.id = s.parent

			WHERE ($ci.key IS NULL OR $ci.$field IS NULL) AND
				$c.level = 0
			GROUP BY $c.id, $ci.key
			HAVING count(s.id) > 0
			Order BY $c.name";

		return $pdo->query($query)->fetchAll($pdo::FETCH_CLASS, self::className());
	}

	/** @todo нуждается в рефакторинге, вынести в CategoriesSearchSql */
	public static function getGlobalCategoryConstrains(array $locations)
	{
		$items = ["'glob'"];
		if (count($locations) == 2) {
			$items[] = "'reg$locations[1]'";
			$items[] = "'loc$locations[1]'";
		} elseif (count($locations) == 3) {
			$items[] = "'loc$locations[1]'";
			$items[] = "'loc$locations[2]'";
		}
		return ' ( ss.meta ?| array[ ' . implode(', ', $items) . ' ] ) ';
	}

	/**
	 * @param $categoryId
	 * @return self
	 */
	public static function findById($categoryId)
	{
		return static::find()->where(['id' => $categoryId])->with('categoriesInfo')->cache()->one();
	}

	/**
	 * @inheritdoc
	 * @return CategoriesQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new CategoriesQuery(get_called_class());
	}

	public function __set($property, $value)
	{
		if ($property == 'services_count') {
			$this->servicesCountHash = $value;
			return true;
		}
		return parent::__set($property, $value);
	}

	//берем свойство из caegoriesInfo, если оно там есть
	public function __get($name)
	{
		if ($name == 'services_count') {
			return $this->servicesCountHash;
		}
		if (in_array($name, array_keys(CategoriesInfo::getTableSchema()->columns))) {
			if ($this->isRelationPopulated('categoriesInfo') && isset($this->categoriesInfo[$name])) {
				return $this->categoriesInfo[$name];
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

	/** @todo временный метод! ВЫПИЛИТЬ, как реализуем топ3 для привязок */
	public function getTop3()
	{
		$pdo = yii::$app->db->getMasterPdo();

		$sql = "SELECT CASE WHEN c.key IS NULL THEN  mc.custom_category_id ELSE  c.id END AS parent, sc.id AS category_id, mcl.service_id
				FROM main_categories mc
				LEFT JOIN categories c ON mc.key = c.key AND c.date_removed IS NULL
				LEFT JOIN main_category_links mcl ON mc.id = mcl.main_category_id
				LEFT JOIN categories sc ON mcl.key = sc.key AND sc.date_removed IS NULL
			WHERE mc.key = '{$this->key}' AND (mc.custom_category_id IS NOT NULL OR c.key IS NOT NULL) AND (mcl.service_id IS NOT NULL OR sc.key IS NOT NULL) ORDER BY mc.c_order, mcl.id";

		$result = [];
		$links = $pdo->query($sql)->fetchAll($pdo::FETCH_ASSOC);
		foreach ($links as $link) {
			if ($link['category_id']) {
				$result[] = self::findById($link['category_id']);
			} elseif ($link['service_id']) {
				$result[] = Services::findById($link['service_id']);
			}
		}
		return array_slice($result, 0, 3);
	}
}