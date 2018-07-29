<?php

namespace common\components\services\senders\messageFormatter;

use common\components\services\Dictionary;
use common\components\services\senders\ApplePushSender;
use common\components\services\senders\GooglePushSender;
use common\models\PaymentTransactions;
use common\models\Users;
use common\components\services\senders\SmsSender;

class NewInvoiceMessage extends AbstractMessageFormatter
{
	protected $sender = SmsSender::class;

	public function prepareMessage(Users $user, $data, $senderName = null)
	{
		$this->sender = $senderName;
		try {
			return $this->message($user, $data);
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * @param Users $user
	 * @param array|PaymentTransactions[] $data
	 * @return array
	 */
	protected function message(Users $user, array $data)
	{
		if (count($data) == 1) {
			return [$this->singleMessage($data[0])];
		}
		if ($this->canBeMultiple) {
			return [$this->complexMessage()];
		}
		$result = [];
		foreach ($data as $item) {
			$result[] = $this->singleMessage($item);
		}
		return $result;
	}

	protected function singleMessage(PaymentTransactions $transaction)
	{
		switch ($this->sender) {
			case ApplePushSender::class:
				return Dictionary::newInvoicePush(['name' => $transaction->service->name, 'sum' => $transaction->sum]);
			case GooglePushSender::class:
				return ['title' => Dictionary::newInvoicePushTitle(), 'text' => Dictionary::newInvoicePush(['name' => $transaction->service->name, 'sum' => $transaction->sum])];
			case SmsSender::class:
			default:
				return Dictionary::newInvoiceSms(['name' => $transaction->service->name, 'sum' => $transaction->sum]);
		}
	}

	protected function complexMessage()
	{
		switch ($this->sender) {
			case ApplePushSender::class:
				return Dictionary::newInvoicePushComplex();
			case GooglePushSender::class:
				return ['title' => Dictionary::newInvoicePushTitle(), 'text' => Dictionary::newInvoicePushComplex()];
			case SmsSender::class:
			default:
				return Dictionary::newInvoiceSmsComplex();
		}
	}
}
