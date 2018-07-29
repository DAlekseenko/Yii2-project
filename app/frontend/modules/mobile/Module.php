<?php

namespace frontend\modules\mobile;

use yii;
use yii\web\Response;

class Module extends yii\base\Module
{
	public $layout = 'main';
	public $controllerNamespace = 'frontend\modules\mobile\controllers';

	public function init()
	{
		Yii::$app->errorHandler->unregister();
		Yii::$app->set('errorHandler', ['class' => 'yii\web\ErrorHandler', 'errorAction' => 'mobile/site/error']);
		Yii::$app->errorHandler->register();

		parent::init();
	}
}