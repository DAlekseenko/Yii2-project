<?php
namespace frontend\controllersAbstract;

use common\models\Categories;
use common\models\virtual\CategoriesSearch;
use common\models\virtual\ServicesSearch;
use frontend\models\virtual\PageSearch;
use yii;

class SearchController extends AbstractController
{
	public function beforeAction($action)
	{
		Yii::$app->response->format = yii\web\Response::FORMAT_JSON;
		return parent::beforeAction($action);
	}

	//запускается при поиске на главной и в категориях
	public function actionPageSearch($value, $category_id = null)
	{
		/**
		 * @var $category Categories
		 * @var $categories CategoriesSearch
		 * @var $services ServicesSearch
		 */
		list ($category, $categories, $services) = PageSearch::search($value, $category_id);

		if ($services || $categories) {
			$content = $this->renderPartial('pageSearch', [
				'categories' => $categories->fetch(100),
				'services' => $services->fetch(100),
				'category' => $category,
			]);
			return [
				'hint' => 'Найдено категорий: ' . $categories->count() . ', услуг: ' . $services->count(),
				'content' => $content
			];
		}

		Yii::$app->response->setStatusCode(404);
		return [
			'hint' => 'По запросу &laquo;<span class="-bold">' . htmlspecialchars($value) . '</span>&raquo; ничего не найдено'
		];
	}
}