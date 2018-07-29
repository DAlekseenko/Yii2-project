<?php

namespace console\controllers;

use common\components\services\NotificationService;
use common\components\services\senders\GooglePushSender;
use common\models\PushTask;
use api\components\amqp\InternalQueue;
use common\models\PushTaskUsers;
use common\models\UserDevices;


/**
 * Class SenderPushMessagesController
 * @package console\controllers
 */
class SenderPushMessagesController extends AbstractCronTask
{
    public function handler(array $params)
    {
		$task = PushTask::find()->where(['status' => PushTask::STATUS_PREPARED])->orderBy('date_create')->limit(1)->one();
		if (!empty($task)) {
			$this->runPush($task);
		}
    }

	/**
	 * @param $task
	 * @return bool
	 */
    public function runPush($task)
	{
		$task->status = PushTask::STATUS_PERFORMED;
		$task->update();
		$date = new \DateTime();
		try {
			$query = PushTaskUsers::find()->where(['push_task_id' => $task->id])->with(['user']);

			$devices = [];
			if ($task->apple) {
				$devices[] = 'apple';
			}
			if ($task->android) {
				$devices[] = 'google';
			}
			$notifications = NotificationService::pushMessages($devices);

			$message = [
				'title' => $task->title,
				'text' => $task->text,
			];

			foreach ($query->each() as $taskUser) {
				if (isset($taskUser->user)) {

					$notifications->addUserData($taskUser->user->user_id, $message);
					$notifications->sendAll();
					$notifications->clearAll();
				}
			}
			$task->status = PushTask::STATUS_SUCCESS;
			$task->update();
			return true;

		} catch (\Exception $e) {
			\yii::info('PUSH WITH ERROR: ' . $e->getMessage());
			$task->status = PushTask::STATUS_FAILURE;
			$task->date_concluding  = $date->format('Y-m-d H:i:s');
			$task->update();
			return false;
		}
	}
}

