<?php

namespace console\models;

use backend\models\Services;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class CategoriesAndServices extends Model
{
	private static $resultCount;

	private static function setServicesPath()
	{
		foreach (range(0, (int) (ServicesTmp::find()->count() / 10000)) as $current) {
			foreach(ServicesTmp::find()->limit(10000)->offset($current*10000)->orderBy('id')->all() as $item) {

				$parents = ArrayHelper::getColumn($item->category->getParentsWithoutCache(1), 'id');
				$item->path =  '{' . implode(',', $parents) . '}';

				$locationIds = ['glob'];
				if (isset($item->location)) {
					$locationIds = ['loc' . $item->location_id];
					$parent = $item->location->parents(1)->select('id')->column();
					if (isset($parent[0])) {
						$locationIds[] = 'reg' . $parent[0];
					}
				}
				$item->meta = json_encode( array_fill_keys($locationIds, $parents) );

				$item->save();
			}
		}
	}

	private static function setCategoriesIntervals()
	{
		foreach (CategoriesTmp::find()->all() as $item) {
			$interval = ServicesTmp::findServiceInterval($item->id);
			$item->s_from = $interval[0];
			$item->s_to = $interval[1];
			$item->save();
		}
	}

	//из-за того, что нельзя просто так взять и удалить все записи, а потом добавить(удалятся все связанные данные), то схема такая:
	//1. Создаем вспомогательные таблицы
	//2. Загружаем в них все из дерева
	//3. Переновим данные из этих таблиц в нормальные с помощью update/insert. Записи, которые надо удалить, помечаем как "удаленные"
	public static function refresh($items)
	{
		self::$resultCount = ['categories' => 0, 'services' => 0];
		Yii::info("Refreshing categories and services...\n-");

		CategoriesTmp::createTable();
		ServicesTmp::createTable();

		self::saveCategoriesAndServices($items);

		self::setServicesPath();
		self::setCategoriesIntervals();


		list(self::$resultCount['countCategoriesUpdate'], self::$resultCount['countCategoriesRemoved'],  self::$resultCount['countCategoriesInsert']) = CategoriesTmp::loadInTable();
		list(self::$resultCount['countServicesUpdate'], self::$resultCount['countServicesRemoved'], self::$resultCount['countServicesInsert']) = ServicesTmp::loadInTable();

		ServicesCount::recount(ServicesTmp::tableName(), CategoriesTmp::tableName());

		CategoriesTmp::dropTable();
		ServicesTmp::dropTable();

		return self::$resultCount;
	}

	private static function saveCategoriesAndServices($items)
	{
		//бежим по этому массиву. На самом верхнем уровне не может быть услуг, только категории. Поэтому makeRoot у категорий
		foreach ($items as $item) {
			if (ServicesTmp::isService($item)) {
				continue;
			}
			self::$resultCount['categories']++;
			$category = new CategoriesTmp();
			$category->setAttributesByItem($item);

			if (!$category->makeRoot()) {
				throw new \Exception('Failed to make category root: ' . serialize($category->getFirstErrors()));
			}

			if (!empty($item['children'])) {
				self::saveCombineChildren($category, $item['children']);
			}
		}
		return self::$resultCount;
	}

	private static function saveCombineChildren(Categories $parent, $children)
	{
		//здесь 2ой уровень и ниже. Тут вперемешку идут и категории, и услуги. Если мы встретили категорию - добавляем её. Если услугу - сохраняем услугу
		foreach ($children as $item) {
			if (ServicesTmp::isService($item)) {
				$item['s_order'] = ++self::$resultCount['services'];
				ServicesTmp::saveService($parent, $item);
			} else {
				self::saveCategory($parent, $item);
			}
		}
	}

	private static function saveCategory($parent, $item)
	{
		self::$resultCount['categories']++;
		$category = new CategoriesTmp();
		$category->setAttributesByItem($item);

		if (!$category->appendTo($parent)) {
			throw new \Exception('Failed to append category child: ' . serialize($category->getFirstErrors()) . ' ' . serialize($item));
		}

		if (!empty($item['children'])) {
			self::saveCombineChildren($category, $item['children']);
		}
	}
}