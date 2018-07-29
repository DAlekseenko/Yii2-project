<?php
namespace common\components\web;

use yii;

class ErrorAction extends yii\web\ErrorAction
{

	public function run()
	{
		if (($exception = Yii::$app->getErrorHandler()->exception) === null) {
			// action has been invoked not from error handler, but by direct route, so we display '404 Not Found'
			$exception = new yii\web\HttpException(404, Yii::t('yii', 'Page not found.'));
		}

		if ($exception instanceof yii\web\HttpException) {
			$code = $exception->statusCode;
		} else {
			$code = $exception->getCode();
		}

		if ($exception instanceof \Exception) {
			$name = $exception->getName();
		} else {
			$name = $this->defaultName ?: Yii::t('yii', 'Error');
		}

		$message = $this->defaultMessage ?: ($exception->getMessage() ?: Yii::t('yii', 'An internal server error occurred.'));

		if (Yii::$app->getRequest()->getIsAjax()) {
			return "$name: $message";
		} else {
			$content = $this->controller->getView()->renderFile($this->getViewPath($code), [
				'name' => $name,
				'message' => $message,
				'exception' => $exception,
				'file' => $exception->getFile(),
				'code' => $code,
				'line' => $exception->getLine(),
				'trace' => $exception->getTrace(),
			], $this->controller);
			return $this->controller->renderContent($content);
		}
	}

	private function getViewPath($code)
	{
		$path = Yii::getAlias('@common/views');

		$view = '/error/' . $code;
		if ($code && file_exists($path . $view . '.php')) {
			return $path . $view . '.php';
		}
		return $path . '/error/index.php';
	}
}