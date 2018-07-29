<?php

namespace api\controllers;

use api\components\services\BelGai\BelGaiHandler;

class BelGaiController extends AbstractController
{
	public function beforeAction($action)
	{
		$this->enableCsrfValidation = false;
		return parent::beforeAction($action);
	}

	/**
	 * @return bool
	 * @throws \PbrLibBelCommon\Exceptions\BelGaiApiException
	 */
	public function actionIndex()
	{
		$logger = \yii::$app->{LOG_CATEGORY_BEL_GAI};
		$message = \yii::$app->request->getRawBody();

		$handler = new BelGaiHandler($logger);
		$handler->handleMessage($message);

		return true;
	}
}
