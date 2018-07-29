<?php

namespace common\models;

use common\components\behaviors\NestedSetsQueryBehavior;
use common\models\Services;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Categories]].
 *
 * @method CategoriesQuery roots()
 * @method CategoriesQuery leaves()
 * @see Categories
 */
class CategoriesQuery extends \common\components\db\ActiveQuery
{

	public function behaviors()
	{
		return [
			'nestedSetsQueryBehavior' => [
				'class' => NestedSetsQueryBehavior::class,
			],
		];
	}

	/**
	 * @param int|array $id можно передавать массив. Тогда будет условие in
	 * @return CategoriesQuery
	 */
	public function byId($id)
	{
		return $this->andWhere([Categories::tableName() . '.id' => $id]);
	}

	public function search($value)
	{
		$tableCi = CategoriesInfo::tableName();
		$this->leftJoin($tableCi, Categories::tableName() . '.key = ' . $tableCi . '.key');
		return $this->andWhere(['or', ['ilike', Categories::tableName() . '.name', $value], ['ilike', $tableCi . '.name', $value]]);
	}

	public function withServices()
	{
		$with = [
			'services' => function (ActiveQuery $query) {
				$si = ServicesInfo::tableName();
				$s = Services::tableName();
				$query
					->leftJoin($si, "$si.service_id = $s.id")
					->where(['in', 'location_id', Locations::getCurrentLocationTreeIds()])
					->andWhere($s . '.date_removed IS NULL')
					->with(['location', 'servicesInfo'])
					->orderBy([$s . '.location_id' => SORT_DESC, 'COALESCE(' . $si . '.success_counter,0)' => SORT_DESC, $s . '.name' => SORT_ASC]);
			},
		];
		return $this->with($with);
	}

	/**
	 * @param string $field show_main | show_top
	 * @return $this
	 */
	public function byShowField($field)
	{
		$field = CategoriesInfo::tableName() . '.' . $field;
		return $this->andWhere(['or', [$field => true], $field . ' is null'])->orderBy($field);
	}

	public function innerJoinWithServicesCount()
	{
		$joinWith = [
			'servicesCount' => function (ActiveQuery $query) {
				$query->where(['location_id' => Locations::getCurrentLocationTreeIds()]);
			},
		];
		return $this->innerJoinWith($joinWith);
	}

	/**
	 * @return CategoriesQuery
	 */
	public function innerJoinServicesCount()
	{
		$on = [
			'and',
			Categories::tableName() . '.id = ' . ServicesCount::tableName() . '.category_id',
			[ServicesCount::tableName() . '.location_id' => Locations::getCurrentLocationTreeIds()],
		];
		return $this->innerJoin(ServicesCount::tableName(), $on);
	}

	/**Возвращает обычный двумерный массив, в котором все поля из таблицы categories заменены на поля из таблицы categoriesInfo(если они там были указаны)
	 * @return array
	 */
	public function allWithInfo()
	{
		$this->with('categoriesInfo')->asArray();

		$result = [];
		foreach ($this->all() as $i => $item) {
			foreach (array_keys($item) as $name) {
				if ($name !== 'categoriesInfo') {
					$result[$i][$name] = isset($item['categoriesInfo'][$name]) ? $item['categoriesInfo'][$name] : $item[$name];
				}
			}
		}
		return $result;
	}

	public function allAsEntities($entityClass)
	{
		$result = [];
		foreach ($this->all() as $item) {
			$result[$item->id] = new $entityClass($item);
		}
		return $result;
	}

	/**
	 * @inheritdoc
	 * @return array|Categories[]
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return Categories|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}