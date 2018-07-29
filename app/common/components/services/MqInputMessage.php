<?php

namespace common\components\services;

use PhpAmqpLib\Message\AMQPMessage;

class MqInputMessage
{
	protected $message;

	public function __construct(AMQPMessage $message)
	{
		$this->message = $message;
	}

	public function getDeliveryTag()
	{
		return $this->message->delivery_info['delivery_tag'];
	}

	public function isValid()
	{
		return true;
	}
}