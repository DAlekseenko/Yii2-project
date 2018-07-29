<?php

namespace common\components\services\senders;

use yii;
use common\models\Users;
use common\components\services\PhoneService;

class SmsSender extends AbstractSender implements SenderInterface
{
	public function applyMessage(Users $user, $data = null)
	{
		// @TODO: Сервис может не принять сообщение есль у нас будут настройки с возможность редактирования уведомлений.
		$message = $this->messageFormatter->prepareMessage($user, $data, self::class);
		if (empty($message)) {
			return false;
		}
		$this->addMessage(['phone' => $user->phone, 'messages' => $message]);
		return true;
	}

	public function send()
	{
		foreach ($this->messages as $message) {
			foreach ($message['messages'] as $text) {
				$sendResult = PhoneService::sendAbstractSms($message['phone'], $text);
				yii::info('SMS message to: ' . $message['phone'] . '; content: "' . $text . '"; status: ' . ($sendResult ? 'SUCCESS' : 'FAIL'), $this->log);
			}
		}
	}
}
