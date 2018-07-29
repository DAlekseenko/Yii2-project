<?php

namespace frontend\models\virtual;

use Yii;
use yii\base\Model;

class SendMailForm extends Model
{
	public $email;

	public function rules()
	{
		return [
			['email', 'required'],
			['email', 'email'],
		];
	}

	public function attributeLabels()
	{
		return [
			'email' => 'e-mail',
		];
	}

	public function sendPaymentsInvoice($paymentHistoryItem)
	{
		$from = Yii::$app->params['onlineHelp']['from'];
		$mail = Yii::$app->mailer->compose('@frontend/views/partial/payments/invoice', ['paymentHistoryItem' => $paymentHistoryItem])
								 ->setFrom($from)
								 ->setTo($this->email)
								 ->setSubject('Квитанция об оплате');
		return $mail->send();
	}
}
