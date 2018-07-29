<?php
/**
 * Created by PhpStorm.
 * User: kirsanov
 * Date: 30.03.2016
 * Time: 14:12
 */

namespace console\controllers;

use Yii;
use common\components\services\MqJobInputMessage;

/**
 * Class JobsReaderController
 * @package console\controllers
 */
class JobsReaderController extends AbstractCronDaemon
{
    const HEARTBEAT = 100;

    public function getQueue()
	{
		return QUEUE_JOBS;
	}

	public function getPattern()
	{
		return '%s > /dev/null 2>&1 &';
	}

	public function afterProcessTask()
	{

	}

    /**
     * @param string[] $params
     * @return void
     */
    public function handler(array $params)
	{
		/** @var \common\components\services\MqConnector $connector*/
		$connector = Yii::$app->amqp;
		$queueManager = $connector->getConnectionManager();

		if ($queueManager->countChanelMessages($this->getQueue()) > 0) {
			while ($request = $queueManager->getSimple($this->getQueue(), MqJobInputMessage::class)) {
				if ($request->isValid()) {
					$command = 'php ' . ROOT_DIR . 'cron/yii-cron-gw.php ' . $request->getMethod() . ' ' . implode(' ', $request->getArgs());
					shell_exec(sprintf($this->getPattern(), $command));
				}
				$queueManager->ackSimple($request);
				$this->afterProcessTask();
			}
		}
	}
}