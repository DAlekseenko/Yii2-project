<?php
namespace frontend\modules\mobile\controllers;

use yii;
use frontend\modules\mobile\components\behaviors\RenderLayout;

/**
 * @mixin RenderLayout
 */
class InvoicesController extends \frontend\controllersAbstract\InvoicesController
{
	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors[] = [
			'class' => RenderLayout::className(), //в шаблоне используются функции из этого поведения, что бы рисовать разные части страницы
		];
		return $behaviors;
	}
}