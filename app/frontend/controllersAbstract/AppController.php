<?php

namespace frontend\controllersAbstract;

use yii;
use common\components\lib\Device;
use yii\web\Cookie;

class AppController extends AbstractController
{
	public function actionIndex()
	{

		if (\yii::$app->clientDevice->isAndroid()) {
			$this->redirect(\yii::$app->clientDevice->androidStoreLink());
		} elseif (\yii::$app->clientDevice->isIos()) {
			$this->redirect(\yii::$app->clientDevice->iosStoreLink());
		} else {
			$this->redirect(EXTERNAL_URL);
		}
	}

	public function actionDownload($id)
	{
		$teaserCookie = [
			'name' => 'teaser_' . $id,
			'value' => 1,
			'expire' => time() + 60*60*24*7,
		];
		$cookies = Yii::$app->response->cookies;
		$cookies->add(new Cookie($teaserCookie));
		$this->redirect('/app');
	}
}
