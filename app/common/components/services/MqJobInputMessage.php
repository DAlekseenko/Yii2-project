<?php

namespace common\components\services;

use PhpAmqpLib\Message\AMQPMessage;

class MqJobInputMessage extends MqInputMessage
{
	protected $method;

	protected $args = [];

	public function __construct(AMQPMessage $message)
	{
		parent::__construct($message);
		$body = json_decode($message->body, 1);

		$this->method = isset($body['method']) ? $body['method'] : null;
		$this->args = isset($body['args']) ? $body['args'] : [];
	}

	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * @return array
	 */
	public function getArgs()
	{
		return $this->args;
	}
}
