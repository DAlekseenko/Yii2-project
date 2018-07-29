<?php

namespace frontend\controllersAbstract;

use yii;
use yii\web\Cookie;

abstract class TeaserController extends AbstractController
{
	/**
	 * Возвращает html код тизера для показа.
	 *
	 * @return string
	 */
	abstract function actionIndex();

	public function actionClose($teaser_id)
	{
		$teaserCookie = [
			'name' => 'teaser_' . $teaser_id,
			'value' => 1,
			'expire' => time() + 60*60*24*7,
		];
		$cookies = Yii::$app->response->cookies;
		$cookies->add(new Cookie($teaserCookie));
	}
}
