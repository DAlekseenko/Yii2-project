<?php

namespace console\models;

use common\components\services\MqCronSmsMessage;

class CheckInvoicesSmsQueue
{
	protected $data = [];

	protected $queue;

	/** @var \common\components\services\MqConnector $connector */
	protected $connector;

	public function __construct($queue, $connector)
	{
		$this->connector = $connector;
		$this->queue = $queue;
	}

	public function add($userId, $uuid)
	{
		if (!isset($this->data[$userId])) {
			$this->data[$userId] = [];
		}
		$this->data[$userId][] = $uuid;
	}

	public function putToQueue()
	{
		foreach ($this->data as $uuidList) {
			$this->connector->sendMessageDirectly(new MqCronSmsMessage($this->queue, $uuidList));
		}
	}
}
