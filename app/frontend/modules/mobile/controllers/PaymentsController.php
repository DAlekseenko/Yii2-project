<?php
namespace frontend\modules\mobile\controllers;

use frontend\modules\mobile\components\behaviors\RenderLayout;
use yii;

class PaymentsController extends \frontend\controllersAbstract\PaymentsController
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