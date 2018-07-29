<?php

namespace frontend\modules\mobile\controllers;

use common\components\lib\Device;

class TeaserController extends \frontend\controllersAbstract\TeaserController
{
	const TEASER_PREFIX = 'teaser_';
	const TEASER_ID_APP_DOWNLOAD = 'app';

	/**
	 *
	 */
	public function actionIndex()
	{
		$teaserName = self::TEASER_PREFIX . self::TEASER_ID_APP_DOWNLOAD;
		if (!isset($_COOKIE[$teaserName]) && (\yii::$app->clientDevice->isAndroid() || \yii::$app->clientDevice->isIos())) {
			return $this->renderPartial($teaserName, ['id' => self::TEASER_ID_APP_DOWNLOAD, 'isAndroid' => \yii::$app->clientDevice->isAndroid()]);
		}
		\yii::$app->response->setStatusCode(404);
	}
}
