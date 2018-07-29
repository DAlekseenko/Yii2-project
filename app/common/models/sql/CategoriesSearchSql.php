<?php

namespace common\models\sql;

use common\models\Categories;
use common\models\CategoriesCustom;
use common\models\CategoriesInfo;
use common\models\Locations;

class CategoriesSearchSql
{
	use EntitySearchHelperTrait;

	/** @var \PDO  */
	protected $pdo;

	protected $query = null;

	/** @var  CategoriesCustom|Categories */
	protected $category;

	protected $locationId;

	protected $sortMode = Categories::SORT_MODE_ALPHABET;

	/** @var array|Categories[]  */
	protected $categoryChildren = null;

	public function __construct(Categories $category = null, $query = null, $locationId = null)
	{
		$this->pdo = \yii::$app->db->getMasterPdo();
		$this->category = $category;
		$this->query = $query;
		$this->locationId = $locationId;

		if ($category instanceof CategoriesCustom) {
			$this->categoryChildren = $category->categories ?: [];
		}
	}

	public function setSortMode($mode)
	{
		$this->sortMode = $mode;
	}

	public function findCategories()
	{
		return $this->resultCategoriesReduce($this->getCategories());
	}

	/**
	 * Возвращает список найденных категорий.
	 * Список сырой, категории не отсортированы, без счетчиков и без учета локаций
	 *
	 * @return array|\common\models\Categories[]
	 */
	public function getCategories()
	{
		if ($this->category instanceof CategoriesCustom && empty($this->categoryChildren)) {
			return [];
		}
		if (isset($this->categoryChildren) && empty($this->query)) {
			return $this->categoryChildren;
		}
		$c = Categories::tableName();
		$ci = CategoriesInfo::tableName();
		$intervalConstrains = $this->getIntervalConstrains();
		$queryConstrains = $this->getQueryConstrains($c, $ci);
		$sql = "
			SELECT $c.id, $c.s_from, $c.s_to
			FROM $c
			LEFT JOIN $ci USING(key)
			WHERE $c.date_removed IS NULL $intervalConstrains $queryConstrains";

		return $this->pdo->query($sql)->fetchAll(\PDO::FETCH_CLASS, Categories::class);
	}

	public function resultCategoriesReduce($categoriesList)
	{
		if (empty($categoriesList)) {
			return false;
		}
		$this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);

		$intervalList = [];
		$ids = [];

		foreach ($categoriesList as $interval) {
			if (!empty($interval['s_from']) && !empty($interval['s_to'])) {
				$intervalList[] = " (ss.s_order >= {$interval['s_from']} AND ss.s_order <= {$interval['s_to']}) ";
				$ids[] = $interval['id'];
			}
		}

		if (empty($intervalList) || empty($ids)) {
			return false;
		}

		$intervalConstrain = ' (' . implode(' OR ', $intervalList) . ') ';
		$idsConstrain = implode(',', $ids);

		$constrains = $this->getGlobalCategoryConstrains(Locations::getCurrentLocationIds($this->locationId));
		$c = Categories::tableName();
		$ci = CategoriesInfo::tableName();

		$order = "$c.name";
		if ($this->sortMode == Categories::SORT_MODE_USER) {
			$order = "coalesce($ci.c_order, 0) DESC, " . $order;
		}

		$query = "
			SELECT $c.*, $ci.name AS info_name, $ci.description AS info_description, $ci.description_short AS info_description_short, count(s.id) AS services_count FROM $c
			LEFT JOIN $ci USING(key)
			LEFT JOIN (
				SELECT ss.id, ss.path FROM services ss WHERE
				$intervalConstrain
				AND $constrains AND ss.path && array[$idsConstrain] AND ss.date_removed IS NULL
			) as s ON ( s.path[$c.level+1] = $c.id )

			WHERE
				$c.id in ($idsConstrain)
			GROUP BY $c.id, $ci.key HAVING count(s.id) > 0 ORDER BY $order;
			";

		return $this->pdo->query($query);
	}

	private function getIntervalConstrains()
	{
		if (isset($this->categoryChildren)) {
			$constrains = [];
			foreach ($this->categoryChildren as $category) {
				$parents = $category->getParents(1);
				$constrains[] = " (lft >= {$category->lft} AND rgt <= {$category->rgt} AND level >= {$category->level} AND tree_id = {$parents[0]->tree_id}) ";
			}
			return $categoryConstrains = empty($constrains) ? '' : ' AND ( ' . implode(' OR ', $constrains) . ' ) ';
		}
		$categoryConstrains = '';
		if (!empty($this->category)) {
			$parents = $this->category->getParents(1);
			$categoryConstrains = " AND (lft > {$this->category->lft} AND rgt < {$this->category->rgt} AND level > {$this->category->level} AND tree_id = {$parents[0]->tree_id}) ";
		}
		return $categoryConstrains;
	}

	private function getQueryConstrains($c, $ci)
	{
		if (empty($this->query)) {
			return '';
		}
		$query = $this->pdo->quote('%' . $this->query . '%');

		return  "AND ( $c.name ilike $query OR $ci.name ilike $query OR $ci.description ilike $query OR $ci.description_short ilike $query)";
	}
}
