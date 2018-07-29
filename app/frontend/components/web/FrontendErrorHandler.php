<?php

namespace frontend\components\web;

use common\components\services\MailService;

class FrontendErrorHandler extends \yii\web\ErrorHandler
{
	public function handleException($exception)
	{
		MailService::sendAdminReportOnException($exception);
		parent::handleException($exception);
	}
}
