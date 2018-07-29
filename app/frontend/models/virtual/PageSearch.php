<?php
namespace frontend\models\virtual;

use common\models\Categories;
use common\models\CategoriesCustom;
use common\models\Services;
use Yii;
use yii\base\Model;

class PageSearch extends Model
{
	const LOG = 'search';

	public static function search($value, $categoryId = null, $categorySearchMode = Categories::SORT_MODE_ALPHABET, $serviceSortMode = Services::SORT_MODE_DEFAULT, $locationId = null)
	{
		$category = self::getCategory($categoryId);

		//ищем категории
		$categoriesSearch = new CategoriesSearch();
		$categoriesSearch->setAttributes(['value' => $value, 'category' => $category, 'locationId' => $locationId]);
		if (!$categoriesSearch->validate()) {
			throw new \yii\web\BadRequestHttpException(implode(' ', $categoriesSearch->getFirstErrors()));
		}
		$categoriesSearch->doSearch($categorySearchMode);

		//ищем услуги
		$servicesSearch = new ServicesSearch();
		$servicesSearch->setAttributes(['value' => $value, 'category' => $category]);
		if (!$servicesSearch->validate()) {
			throw new \yii\web\BadRequestHttpException(implode(' ', $servicesSearch->getFirstErrors()));
		}
		$servicesSearch->doSearch($serviceSortMode);

		yii::info("Search phrase: $value; categories found: {$categoriesSearch->count()}; services found: {$servicesSearch->count()}; category: $categoryId", self::LOG);

		return [$category, $categoriesSearch, $servicesSearch];
	}

	protected static function getCategory($categoryId)
	{
		if ($categoryId) {
			$category = $categoryId < 10000000000 ? CategoriesCustom::find()->where(['id' => $categoryId])->withServices()->one() : Categories::findById($categoryId);
			if (!$category) {
				throw new \yii\web\BadRequestHttpException('Bad categoryId');
			}
			return $category;
		}
		return null;
	}
}