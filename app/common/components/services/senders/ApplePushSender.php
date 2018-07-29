<?php

namespace common\components\services\senders;

use common\models\Users;
use common\models\UserDevices;

class ApplePushSender extends AbstractSender implements SenderInterface
{
	private $config;

	/** @var \ApnsPHP_Push */
	private $apns;

	protected function init()
	{
		ob_start();
		$this->config = \yii::$app->params['apns'];
		$feedback = new \ApnsPHP_Feedback(\ApnsPHP_Abstract::ENVIRONMENT_PRODUCTION, $this->config['cert']);

		$feedback->setProviderCertificatePassphrase($this->config['passphrase']);
		$feedback->setRootCertificationAuthority($this->config['rootCert']);
		$feedback->connect();

		$aDeviceTokens = $feedback->receive();
		if (!empty($aDeviceTokens)) {
			$tokens = [];
			foreach ($aDeviceTokens as $DeviceToken) {
				/**
				 * формат
				 * [timestamp] => 1406040206
				 * [tokenLength] => 32
				 * [deviceToken] => 738d005a11bca268e2f1bffbfed88a456e261020b9277883cde14d9c8f47cde0
				 */
				\yii::info('Feedback - Удаленный токен ' . $DeviceToken['deviceToken'], $this->log);
				$tokens[] = $DeviceToken['deviceToken'];
			}
			UserDevices::clearApiTokens($tokens);
		}
		$feedback->disconnect();
		ob_end_clean();
	}

	public function applyMessage(Users $user, $data = null)
	{
		$tokens = UserDevices::findApplePushTokensByUserId($user->user_id);
		if (empty($tokens)) {
			return false;
		}
		$messages = $this->messageFormatter->prepareMessage($user, $data, self::class);
		if (empty($messages)) {
			return false;
		}
		$this->addMessage(['tokens' => $tokens, 'messages' => $messages, 'user' => $user]);

		return true;
	}

	public function send()
	{
		ob_start();
		if (!empty($this->messages)) {
			$this->apns = new \ApnsPHP_Push(\ApnsPHP_Abstract::ENVIRONMENT_PRODUCTION, $this->config['cert']);

			$this->apns->setProviderCertificatePassphrase($this->config['passphrase']);
			$this->apns->setRootCertificationAuthority($this->config['rootCert']);
			$this->apns->connect();

			foreach ($this->messages as $message) {
				foreach ($message['messages'] as $text) {
					$this->sendOne($message['tokens'], $text, $message['user']->phone);
				}
			}

			$this->apns->disconnect();
		}
		ob_end_clean();
	}

	private function sendOne(array $tokens, $text, $phone)
	{
		try {
			$message = new \ApnsPHP_Message();

			foreach ($tokens as $token) {
				$message->addRecipient($token);
			}
			$message->setCustomIdentifier($phone);
			$message->setText($text);
			$message->setBadge(1);
			$this->apns->add($message);
			$this->apns->send();

			$aErrorQueue = $this->apns->getErrors();
			$deleteTokens = [];
			if (!empty($aErrorQueue)) {
				\yii::warning('Ошибка отправки ios (' . $phone . ') -  ' . print_r($aErrorQueue, true), $this->log);
				if (is_array($aErrorQueue)) {
					foreach ($aErrorQueue as $error) {
						if (isset($error['ERRORS']) && is_array($error['ERRORS'])) {
							foreach ($error['ERRORS'] as $m) {
								if (isset($m['statusMessage']) && $m['statusMessage'] == 'Invalid token') {
									$arrayId = $m['identifier'] - 1;
									if (isset($tokens[$arrayId])) {
										$deleteTokens[] = $tokens[$arrayId];
										\yii::info('Удален ошибочный токен ' . $tokens[$arrayId], $this->log);
									}
								}
							}
						}
					}
				}
			} else {
				\yii::info('Push Apple message to: ' . $phone . '; content: "' . $text . '"', $this->log);
			}
			UserDevices::clearApiTokens($deleteTokens);
		} catch (\Exception $e) {
			\yii::error($e->getMessage(), $this->log);
		}
	}
}
