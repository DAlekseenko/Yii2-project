<?php

namespace frontend\modules\mobile\controllers;

use frontend\modules\mobile\components\behaviors\RenderLayout;

class HelpController extends \frontend\controllersAbstract\HelpController
{
	public function behaviors()
	{
		return [
			//в шаблоне используются функции из этого поведения, что бы рисовать разные части страницы
			'renderLayout' => [
				'class' => RenderLayout::className(),
			],
		];
	}
}