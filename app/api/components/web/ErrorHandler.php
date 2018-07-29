<?php
namespace api\components\web;

use yii;
use yii\base\UserException;
use yii\web\HttpException;
use yii\base\ErrorException;
use yii\base\Exception;

class ErrorHandler extends yii\web\ErrorHandler
{
	protected function convertExceptionToArray($exception)
	{
		Yii::error($exception, 'rest');
		if (!YII_DEBUG && !$exception instanceof UserException && !$exception instanceof HttpException) {
			$exception = new HttpException(500, 'There was an error at the server.');
		}

		Yii::$app->response->setApiResponseFormat(Response::API_FORMAT_AS_IS);
		return [
			'name' => ($exception instanceof Exception || $exception instanceof ErrorException) ? $exception->getName() : 'Exception',
			'status' => $exception instanceof HttpException ? $exception->statusCode : 500,
			'error' => $exception->getMessage(),
		];
	}
}