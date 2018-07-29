<?php
namespace api\controllers;

use common\models\CategoriesCustom;
use common\models\Services;
use yii;
use api\components\formatters\EntitiesFormatter;
use common\models\Recommendations;
use common\models\Categories;
use frontend\controllersAbstract\PaymentsController;

class CategoriesController extends AbstractController
{
	public function actionGet($parent_id = null, $sort_mode = Categories::SORT_MODE_USER, $location_id = null)
	{
		$result = [];
		$targetCategoryPath = [];
		// если родитель не указан
		if (empty($parent_id)) {
			$indexParams = PaymentsController::getIndexParams($location_id);

			if (isset($indexParams['roots']) && is_array($indexParams['roots'])) {
				$result = EntitiesFormatter::categorySetFormatter($indexParams['roots'], $indexParams['children']);
			}
		} else {
			$closestList = Categories::getClosestCategories($parent_id, $sort_mode, $location_id);
			if ($closestList['category'] instanceof CategoriesCustom) {
				$targetCategoryPath = [$closestList['category']];
			} else {
				$targetCategoryPath = $closestList['category']->getParents(true);
			}
			if (isset($closestList['closestChildren']) && is_array($closestList['closestChildren'])) {
				$result = EntitiesFormatter::categorySetFormatter($closestList['closestChildren']);
			}
			if (isset($closestList['category']->services)) {
				$result = array_merge($result, EntitiesFormatter::serviceSetFormatter($closestList['category']->services));
			}
		}

		return ['data' => $result, 'target_category_path' => EntitiesFormatter::categorySetFormatter($targetCategoryPath)];
	}

	/**
	 * @param  $service_id
	 * @return array
	 * @throws yii\web\NotFoundHttpException
	 */
	public function actionGetService($service_id)
	{
		$service = Services::find()->with(['servicesInfo', 'category'])->where(['id' => $service_id])->one();
		if (!empty($service)) {
			return EntitiesFormatter::serviceFormatter($service);
		}
		throw new yii\web\NotFoundHttpException('Service was not found');
	}

	/**
	 * @param $category_id
	 * @return array
	 * @throws yii\web\NotFoundHttpException
	 */
	public function actionGetCategoryPath($category_id)
	{
		$category = Categories::find()->with(['categoriesInfo'])->where(['id' => $category_id])->one();
		if (empty($category)) {
			throw new yii\web\NotFoundHttpException('Category was not found');
		}
		return EntitiesFormatter::categorySetFormatter($category->getParents(true));
	}

	public function actionGetRecommendations()
	{
		Yii::info('Getting recommendations', 'rest');
		$list = [];
		/** @var Recommendations $recommendations */
		$recommendations = Recommendations::findCurrent();
		foreach ($recommendations as $recommendation) {
			$entity = $recommendation->getEntity();
			$parent = $recommendation->getEntityParent();
			$list[] = [
				'id'         => $entity->id,
				'banner_img' => substr(EXTERNAL_URL, 0, -1) . $recommendation->getSrc(),
				'entity'     => $recommendation->isService() ? EntitiesFormatter::serviceFormatter($entity) : EntitiesFormatter::categoryFormatter($entity),
				'parent'     => $parent ? EntitiesFormatter::categoryFormatter($parent) : null
			];
		}

		Yii::info($list, 'rest');
		return [
			'links' => $list
		];
	}
}

