<?php
/**
 * Created by PhpStorm.
 * User: kirsanov
 * Date: 01.04.2016
 * Time: 14:57
 */

namespace common\components\services;

use PhpAmqpLib\Message\AMQPMessage;

class MqCronSmsMessageInput extends MqInputMessage
{
	protected $content;

	public function __construct(AMQPMessage $message)
	{
		parent::__construct($message);
		$body = json_decode($message->body, 1);

		$this->content = isset($body['transactionList']) ? $body['transactionList'] : [];
	}

	public function getContent()
	{
		return $this->content;
	}
}