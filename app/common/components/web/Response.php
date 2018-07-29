<?php
namespace common\components\web;
use yii;

class Response extends \yii\web\Response {
	
	public function redirect($url, $statusCode = 302, $checkAjax = true) {
		//во всех ie нельзя отправлять 302 код без заголовка location
		//в js yii перехватит заголовок X-Redirect и средиректит даже без 302 статуса
		if ($checkAjax && (Yii::$app->getRequest()->getIsPjax() || Yii::$app->getRequest()->getIsAjax())) {
			$statusCode = 200;
		}
		return parent::redirect($url, $statusCode, $checkAjax);
	}
}