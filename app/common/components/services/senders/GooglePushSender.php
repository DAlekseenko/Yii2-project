<?php

namespace common\components\services\senders;

use CodeMonkeysRu\GCM\Sender;
use common\models\UserDevices;
use common\models\Users;

class GooglePushSender extends AbstractSender implements SenderInterface
{
	private $googleKey;

	public function init()
	{
		$this->googleKey = \yii::$app->params['GCM']['token'];
	}

	public function applyMessage(Users $user, $data = null)
	{
		$tokens = UserDevices::findGooglePushTokensByUserId($user->user_id);
		if (empty($tokens)) {
			return false;
		}
		$pushData = $this->messageFormatter->prepareMessage($user, $data, self::class);
		if (empty($pushData)) {
			return false;
		}
		$this->addMessage(['tokens' => $tokens, 'pushData' => $pushData, 'user' => $user]);

		return true;
	}

	public function send()
	{
		if (!empty($this->messages)) {
			$sender = new Sender($this->googleKey);

			foreach ($this->messages as $message) {
				foreach ($message['pushData'] as $pushItem) {
					$this->sendOne($sender, $message['tokens'], $pushItem, $message['user']->phone);
				}
			}
		}
	}

	private function sendOne(Sender $sender, array $tokens, $pushItem, $phone)
	{
		try {
			$message = new \CodeMonkeysRu\GCM\Message($tokens, $pushItem);
			$response = $sender->send($message);

			if ($response->getFailureCount() > 0) {
				$deleteTokens = [];
				$invalidRegistrationIds = $response->getInvalidRegistrationIds();
				foreach ($invalidRegistrationIds as $invalidRegistrationId) {
					//Remove $invalidRegistrationId from DB
					// на входе значение APS91bFY-2CYrriS-Dt6y9_dGHhkPVwy7njqFpfgpzGYlDT4l0SQeqKr-lc1OM0a2DQ33S3EKwy2YJn-upKxOT6rNwgk350xUM3g8VX65rkGocOQX80Ta34pwXo6fyn-usoaGUAm4lzsqbCL-gkzHZZXRX39kUQfnA
					\yii::info('Push Google token for delete: "' . $invalidRegistrationId . '"', $this->log);
					$deleteTokens[] = $invalidRegistrationId;
				}
				UserDevices::clearApiTokens($deleteTokens);
			}
			if ($response->getSuccessCount()) {
				\yii::info('Push Google message to: ' . $phone . '; content: "' . print_r($pushItem, true) . '"', $this->log);
			}
		} catch (\CodeMonkeysRu\GCM\Exception $e) {
			\yii::error('Ошибка отправления на андроид (' . $phone . ') ' . $e->getCode() . ' ' . $e->getMessage(), $this->log);
		}
	}
}
