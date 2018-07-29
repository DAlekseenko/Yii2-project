<?php
namespace frontend\models\virtual;

use common\models\Categories;
use Yii;
use yii\helpers\Url;

class CategoriesSearch extends \common\models\virtual\CategoriesSearch
{
	protected function prepareCategoryAutocomplete(Categories $category)
	{
		$result = parent::prepareCategoryAutocomplete($category);
		$result['url'] = '/categories?id=' . $category['id'];
		$parents = $category->getCategoryNamePath();
		if ($parents) {
			$result['categoryPath'] = implode(' / ', $parents);
		}
		return $result;
	}
}
