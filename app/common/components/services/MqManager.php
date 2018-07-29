<?php

namespace common\components\services;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class MqManager
{
	/** @var \PhpAmqpLib\Connection\AMQPConnection */
	protected $connection;

	public function __construct(AMQPConnection $connection)
	{
		$this->connection = $connection;
	}

	public function __destruct()
	{
		$this->connection->close();
	}

	/**
	 * @param $chanelName
	 * @param MqInputMessage $msgClass
	 * @return MqRequestMessage|null
	 */
	public function getSimple($chanelName, $msgClass = MqInputMessage::class)
	{
		$channel = $this->connection->channel(1);
		$msg = $channel->basic_get($chanelName, false, null);

		if (!$msg || count($msg) == 0) {
			return null;
		}
		return new $msgClass($msg);
	}

	public function ackSimple(MqInputMessage $message)
	{
		$this->connection->channel(1)->basic_ack($message->getDeliveryTag());
	}

	public function nackSimple(MqInputMessage $message)
	{
		$this->connection->channel(1)->basic_nack($message->getDeliveryTag());
	}

	public function countChanelMessages($chanelName)
	{
		$res = $this->connection->channel(1)->queue_declare($chanelName, true);

		return isset($res[1]) ? intval($res[1]) : null;
	}

	public function sendSimple(MqOutputMessageAbstract $message)
	{
		try {
			$msg = new AMQPMessage($message, [
				'delivery_mode' => 2,
			]);

			$channel = $this->connection->channel(2);
			$channel->basic_publish($msg, null, $message->getAnswerTo());
			$channel->close();

			return true;
		} catch (\Exception $e) {
			\yii::error("CAN NOT PUSH MESSAGE! \n ERROR: {$e->getMessage()} \n QUEUE: {$message->getAnswerTo()} \n MESSAGE: {$message}", LOG_CATEGORY_RABBIT_MQ);
			return false;
		}
	}
}
