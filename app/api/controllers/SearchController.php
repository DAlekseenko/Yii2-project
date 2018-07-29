<?php
namespace api\controllers;

use yii;
use frontend\models\virtual\PageSearch;
use api\components\formatters\EntitiesFormatter;
use common\models\Services;
use common\models\Categories;
use common\models\CategoriesCustom;

class SearchController extends AbstractController
{

	public function actionIndex($value, $category_id = null, $location_id = null)
	{
		/**
		 * @var $category \common\models\Categories
		 * @var $categories \common\models\virtual\CategoriesSearch
		 * @var $services \common\models\virtual\ServicesSearch
		 */
		list ($category, $categories, $services) = PageSearch::search(
			$value,
			$category_id,
			Categories::SORT_MODE_ALPHABET,
			Services::SORT_MODE_DEFAULT,
			(int) $location_id ?: null
		);

		if (empty($category)) {
			$targetCategoryPath = [];
		} else if ($category instanceof CategoriesCustom) {
			$targetCategoryPath = [$category];
		} else {
			$targetCategoryPath = $category->getParents(true);
		}

		$result = array_merge(
			EntitiesFormatter::categorySetFormatter($categories->fetch(100)),
			EntitiesFormatter::serviceSetFormatter($services->fetch(100))
		);

		return ['data' => $result, 'count_categories' => $categories->count(), 'count_services' => $services->count(), 'target_category_path' => EntitiesFormatter::categorySetFormatter($targetCategoryPath)];
	}

	public function actionAutocompleteSearch($value, $category_id = null, $location_id = null)
	{
		/**
		 * @var $category \common\models\Categories
		 * @var $categoriesSearch \common\models\virtual\CategoriesSearch
		 * @var $servicesSearch \common\models\virtual\ServicesSearch
		 */
		list ($category, $categoriesSearch, $servicesSearch) = PageSearch::search(
			$value,
			$category_id,
			Categories::SORT_MODE_USER,
			Services::SORT_MODE_POPULAR,
			(int) $location_id ?: null
		);

		return [
			'categories' => $categoriesSearch->fetchAutocomplete(5),
			'services' => $servicesSearch->fetchAutocomplete(5),
			'categories_count' => $categoriesSearch->count(),
			'services_count' => $servicesSearch->count()
		];
	}
}