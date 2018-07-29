<?php

namespace common\components\services\senders;

use common\components\services\senders\messageFormatter\MessageFormatterInterface;

abstract class AbstractSender implements SenderInterface
{
	protected $log = 'notifications';

	/** @var MessageFormatterInterface */
	protected $messageFormatter;

	protected $messages = [];

	public function __construct(MessageFormatterInterface $messageFormatter)
	{
		$this->messageFormatter = $messageFormatter;
		$this->init();
	}

	protected function init()
	{
	}

	protected function addMessage($message)
	{
		$this->messages[] = $message;
	}

	public function clearData()
	{
		$this->messages = [];
	}
}
