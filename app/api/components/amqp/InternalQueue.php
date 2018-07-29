<?php

namespace api\components\amqp;

use PbrLibBelCommon\Protocol\Amqp\AmqpWrapper;
use PhpAmqpLib\Message\AMQPMessage;
use yii\db\Exception;

/**
 * Class InternalQueue
 * @package api\components\amqp
 */
class InternalQueue extends AmqpWrapper
{
    /**
     * @var
     */
    protected $logger;

    /**
     * @var string
     */
    protected $routingParam;

    /**
     * @param null $logger
     * @param string $server
     * @param string $login
     * @param string $password
     * @param string $channelName
     * @param int|null $port
     */
    public function __construct($logger = null, $server, $login, $password, $channelName, $port = null)
    {
        $this->logger = $logger;
        $this->routingParam = $channelName;
        parent::__construct($server, $login, $password, null, $port);
    }

    /**
     * @return null|string
     */
    public function pullTaskId()
    {
        $this->setChannelName($this->routingParam);
        /** @var AMQPMessage $msg */
        $msg = parent::pullMessageFromQueue();
        if (null !== $msg) {
            $id = $msg->body;
            $this->sendAck($msg->delivery_info['delivery_tag']);
            return $id;
        }
        return null;
    }

    /**
     * @param integer $id
     * @throws \Exception
     */
    public function pushTaskId($id)
    {

        $msg = new AMQPMessage($id, [
            'content_type' => 'text/plain',
            'delivery_mode' => 2,
            'application_headers' => []
        ]);
        try {
            $this->setChannelName(null);
            parent::pushMessageToQueue($msg, $this->routingParam);
        } catch (\Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

}