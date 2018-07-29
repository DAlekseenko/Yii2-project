<?php
namespace frontend\controllersAbstract;

use common\models\Categories;
use common\components\services\JsSettings;
use common\models\Services;
use yii;
use frontend\components\behaviors\AjaxEmptyLayout;

class CategoriesController extends AbstractController
{
	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			'ajaxEmptyLayout' => [
				'class' => AjaxEmptyLayout::className(),
			],
		];
	}

	public function actionIndex($id)
	{
		$id = (int) $id;
		if (empty($id)) {
			throw new yii\web\NotFoundHttpException;
		}
		JsSettings::set('categoryId', $id);
		return $this->render('index', Categories::getClosestCategories($id));
	}
}
