<?php

namespace console\controllers;

use api\models\admin\search\UsersSearch;
use common\models\PushTask;
use yii\console\Controller;

class PreparePushTaskController extends Controller
{
	public function actionFromQuery($taskId, $serializedSearchModel)
	{
		$task = PushTask::findById($taskId);
		/** @var UsersSearch $searchModel */
		$searchModel = unserialize(base64_decode($serializedSearchModel));
		if (!empty($task) && !empty($searchModel)) {
			$task->status = PushTask::STATUS_PREPARED;
			$task->save();

			$query = $searchModel->getQuery();
			$query = $searchModel->frontSearch($query, true);
			$query = $task->getNecessaryUsers($query);
			$task->setPushUsersTask($query);
		}
	}
}
