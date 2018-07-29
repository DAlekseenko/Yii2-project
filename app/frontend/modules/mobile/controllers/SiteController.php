<?php
namespace frontend\modules\mobile\controllers;

use common\models\Locations;
use frontend\modules\mobile\components\behaviors\RenderLayout;
use frontend\modules\mobile\components\web\ErrorAction;
use yii;

class SiteController extends \frontend\controllersAbstract\SiteController
{

	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors[] = [
			'class' => RenderLayout::className(), //в шаблоне используются функции из этого поведения, что бы рисовать разные части страницы
		];
		return $behaviors;
	}

	public function actions()
	{
		$actions = parent::actions();
		$actions['error'] = [
			'class' => ErrorAction::class,
		];
		return $actions;
	}

	public function actionIndex()
	{
		return $this->run('/mobile/payments/index');
	}

	public function actionLocationSelect()
	{
		$regions = Locations::find()->roots()->select(['id', 'name'])->with('cities')->orderBy('name')->cache()->all();
		return $this->render('locationSelect', ['regions' => $regions]);
	}
}