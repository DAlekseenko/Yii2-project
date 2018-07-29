<?php
namespace frontend\modules\desktop\models\virtual;

use common\models\Categories;
use frontend\models\virtual\CategoriesSearch;
use frontend\models\virtual\ServicesSearch;
use Yii;
use yii\base\Model;

class AutocompleteSearch extends Model
{
	public static function search($value, $categoryId = null)
	{
		$category = self::getCategory($categoryId);

		$searchModel = new CategoriesSearch();
		$searchModel->setAttributes(['value' => $value, 'category' => $category]);
		if (!$searchModel->validate()) {
			throw new \yii\web\BadRequestHttpException(implode(' ', $searchModel->getFirstErrors()));
		}
		$categories = $searchModel->searchCategoriesAutocomplete();

		$searchModel = new ServicesSearch();
		$searchModel->setAttributes(['value' => $value, 'category' => $category]);
		if (!$searchModel->validate()) {
			throw new \yii\web\BadRequestHttpException(implode(' ', $searchModel->getFirstErrors()));
		}
		$services = $searchModel->searchServicesAutocomplete();

		return [$categories, $services];
	}

	protected static function getCategory($categoryId)
	{
		if ($categoryId) {
			$category = Categories::findById($categoryId);
			if (!$category) {
				throw new \yii\web\BadRequestHttpException('Bad categoryId');
			}
			return $category;
		}
		return false;
	}
}