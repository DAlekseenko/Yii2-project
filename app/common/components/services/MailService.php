<?php

namespace common\components\services;

use yii\web\NotFoundHttpException;

class MailService
{
    public static function sendAdminReportOnException(\Exception $exception)
    {
        $to = \Yii::$app->params['errorReportTo'];

        $skip = $exception instanceof NotFoundHttpException && // Убираем спам на ненайденные картинки
            preg_match('/\.png$/i', $_SERVER['SCRIPT_URL']);   //

        if (!empty($to) && !YII_DEBUG && !$skip) {
            \Yii::$app->mailer
                ->compose('@frontend/views/mail/error-report', ['exception' => $exception])
                ->setFrom('monitoring@dengi.mts.by')
                ->setTo($to)
                ->setSubject('Ошибка на сайте МТС деньги')->send();
        }
    }

    public static function sendAdminReportOnTransactionCancelFail($paymentId, array $request = null, array $result = null)
    {
        $to = \Yii::$app->params['errorReportTo'];

        if (!empty($to) && !YII_DEBUG) {
            \Yii::$app->mailer
                ->compose('@frontend/views/mail/transaction-cancel-error', ['paymentId' => $paymentId, 'request' => $request, 'result' => $result])
                ->setFrom('monitoring@dengi.mts.by')
                ->setTo($to)
                ->setSubject('Ошибка отмены транзакции: ' . $paymentId)->send();
        }
    }

    public static function sendAdminReportOnUnableConfirmTransaction($paymentId)
	{
		$to = [
			'ddk@pbr24.ru',
			'knn@pbr24.ru',
			'ada@pbr24.ru',
			'lt@immo.by'
		];

		if (!empty($to) && !YII_DEBUG) {
			\Yii::$app->mailer
				->compose()
				->setFrom('confirm-transaction@dengi.mts.by')
				->setTo($to)
				->setSubject('Ошибка подтверждения транзакции: ' . $paymentId)
				->setTextBody('Не удалось подтвердить транзакцию после 5ти попыток :(')
				->send();
		}
	}

    public static function sendStateReportCSVOnCheckInvoicesPrepare($file)
    {
        $to = [
            'ddk@pbr24.ru',
            'knn@pbr24.ru',
            'ada@pbr24.ru',
            'lt@immo.by'
        ];
        if (is_file($file)) {
            \Yii::$app->mailer
                ->compose()
                ->attach($file)
                ->setFrom('report@dengi.mts.by')
                ->setTo($to)
                ->setSubject('Отчет изменения состояния cчетов.')
                ->setTextBody('Отчет во вложение.')
                ->send();
        }
    }
}
