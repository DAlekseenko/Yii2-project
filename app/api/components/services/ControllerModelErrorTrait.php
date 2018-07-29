<?php

namespace api\components\services;

use yii;
use yii\base\Model;

trait ControllerModelErrorTrait
{
	protected function returnFieldError(Model $model)
	{
		$errors = $model->getFirstErrors();
		Yii::$app->response->setStatusCode(499);
		Yii::$app->response->setResponseMessage(reset($errors));

		return $errors;
	}
}
