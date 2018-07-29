<?php

namespace common\components\services;

use PhpAmqpLib\Message\AMQPMessage;

class MqRequestMessage extends MqInputMessage
{
	protected $connectionId;

	protected $answerTo;

	protected $requestId;

	protected $method;

	protected $userIp;

	protected $args = [];

	protected $msgBody;

	public function __construct(AMQPMessage $message)
	{
		parent::__construct($message);
		$body = json_decode($message->body, 1);

		$this->msgBody = $message->body;

		$this->connectionId = isset($body['connection_id']) ? $body['connection_id'] : null;
		$this->answerTo = isset($body['answer_to']) ? $body['answer_to'] : null;
		$this->requestId = isset($body['request']['request_id']) ? $body['request']['request_id'] : null;
		$this->userIp = isset($body['x_real_ip']) ? $body['x_real_ip'] : null;
		$this->method = isset($body['request']['method']) ? $body['request']['method'] : null;
		$this->args = isset($body['request']['args']) ? $body['request']['args'] : [];
	}

	public function getConnectionId()
	{
		return $this->connectionId;
	}

	public function getAnswerTo()
	{
		return $this->answerTo;
	}

	public function getRequestId()
	{
		return $this->requestId;
	}

	public function getMethod()
	{
		return $this->method;
	}

	public function getUserIp()
	{
		return $this->userIp;
	}

	/**
	 * @return array
	 */
	public function getArgs()
	{
		return $this->args;
	}

	public function getMsgBody()
	{
		return $this->msgBody;
	}
}
