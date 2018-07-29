<?php

namespace common\models\sql;

use common\models\Categories;
use common\models\CategoriesCustom;
use common\models\Services;
use common\models\Locations;

class ServicesSearchSql
{
	use EntitySearchHelperTrait;

	/** @var \PDO  */
	protected $pdo;

	protected $query = null;

	/** @var  CategoriesCustom|Categories */
	protected $category;

	protected $sortMode = Categories::SORT_MODE_ALPHABET;

	/** @var array|Categories[]  */
	protected $categoryChildren = null;

	protected $services = null;

	public function __construct(Categories $category = null, $query = null)
	{
		$this->pdo = \yii::$app->db->getMasterPdo();
		$this->category = $category;
		$this->query = $query;

		if ($category instanceof CategoriesCustom) {
			$this->categoryChildren = $category->categories ?: [];
			$this->services = $category->services ?: [];
		}
	}

	public function setSortMode($mode)
	{
		$this->sortMode = $mode;
	}

	public function findServices()
	{
		$this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
		$val = $this->pdo->quote('%' . $this->query . '%');

		$tagsConstrains = '';
		$tagsVal = [];
		foreach (explode(' ', $this->query) as $word) {
			if (empty($word)) {
				continue;
			}
			$tagsVal[] = ' si.tags ilike ' . $this->pdo->quote('%' . $word . '%');
		}
		if (!empty($tagsVal)) {
			$tagsConstrains = ' OR (' . implode(' AND ', $tagsVal) . ') ';
		}

		$categoryConstrains = $this->getParentConstrains();
		$locConstrains = $this->getGlobalCategoryConstrains(Locations::getCurrentLocationIds());

		switch ($this->sortMode) {
			case Services::SORT_MODE_POPULAR:
				$order = 'coalesce(si.success_counter, 0) DESC, ss.name ASC';
				break;
			case Services::SORT_MODE_DEFAULT:
			default:
				$order = 'ss.location_id DESC, coalesce(si.success_counter, 0) DESC, ss.name ASC';
				break;
		}

		$query = "
			SELECT ss.*, si.name AS info_name, si.description AS info_description, si.description_short AS info_description_short FROM services ss
			LEFT JOIN services_info as si ON ss.id = si.service_id
			WHERE $locConstrains AND ss.date_removed IS NULL AND
				(ss.name ilike $val OR si.name ilike $val OR si.description ilike $val OR si.description_short ilike $val $tagsConstrains)
				$categoryConstrains
			ORDER BY $order
		";

		return $this->pdo->query($query);
	}

	private function getParentConstrains()
	{
		if (isset($this->categoryChildren)) {
			if (empty($this->categoryChildren) && empty($this->services)) {
				return ' AND FALSE ';
			}

			$categoriesIds = [];
			foreach ($this->categoryChildren as $item) {
				$categoriesIds[] = $item->id;
			}

			$servicesIds = [];
			foreach ($this->services as $item) {
				$servicesIds[] = $item->id;
			}

			$categoryConstrains = [];
			if (!empty($categoriesIds)) {
				$categoryConstrains[] = ' array[' . implode(',', $categoriesIds) . '] && ss.path ';
			}
			if (!empty($servicesIds)) {
				$categoryConstrains[] = ' ss.id IN (' .  implode(',', $servicesIds) .') ';
			}

			return ' AND ( ' . implode(' OR ', $categoryConstrains) . ') ';
 		}
		return empty($this->category) ? '' : ' AND array[' . $this->category->id . '] && ss.path ';
	}
}
