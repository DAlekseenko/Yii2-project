<?php

namespace api\controllersAdmin;

use api\components\web\AdminApiController;
use common\models\PushTask;

class PushReportController extends AdminApiController
{
    /**
     * Lists all PushTask models.
     * @return mixed
     */
    public function actionGet()
    {
        return [
            'list' => PushTask::find()->orderBy('id DESC')->all(),
            'statusList' => PushTask::statusList
        ];
    }
}
