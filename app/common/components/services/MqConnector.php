<?php

namespace common\components\services;

use PhpAmqpLib\Connection\AMQPConnection;

class MqConnector
{
	public $host = '127.0.0.1';

	public $port = 5672;

	public $user = '';

	public $password = '';

	public $vhost = '/';

	/**
	 * @return MqManager
	 */
	public function getConnectionManager()
	{
		return new MqManager(new AMQPConnection($this->host, $this->port, $this->user, $this->password, $this->vhost));
	}

	public function sendMessageDirectly(MqOutputMessageAbstract $message)
	{
		$manager = $this->getConnectionManager();
		$manager->sendSimple($message);
	}
}
