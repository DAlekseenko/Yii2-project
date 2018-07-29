<?php
namespace frontend\modules\desktop\controllers;

use common\models\PaymentFavorites;
use yii;
use frontend\modules\desktop\components\behaviors\RenderLayout;

/**
 * @mixin RenderLayout
 */
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
