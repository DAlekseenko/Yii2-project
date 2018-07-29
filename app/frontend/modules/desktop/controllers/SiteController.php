<?php
namespace frontend\modules\desktop\controllers;

use yii;
use frontend\modules\desktop\components\behaviors\RenderLayout;

/**
 * @mixin RenderLayout
 */
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

	public function actionIndex()
	{
		return $this->run('/desktop/payments/index');
	}
}