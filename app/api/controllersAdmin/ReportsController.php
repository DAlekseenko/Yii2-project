<?php

namespace api\controllersAdmin;

use api\components\web\AdminApiController;
use api\models\admin\Users;
use common\components\services\builders\readers\FileReader;
use common\models\Reports;


class ReportsController extends AdminApiController
{
    /**
     * Lists all Reports models.
     * @return mixed
     */
    public function actionGet()
    {
        $reports = Reports::find();
        $id = \Yii::$app->user->id;
        $roles = \Yii::$app->authManager->getRolesByUser($id);

        if (!isset($roles[Users::ROLE_ADMIN])) {
            $reports->where(['leader_id' => $id]);
        }

        $reports->orderBy('id DESC');

        return [
            'list' => $reports->all(),
            'statusList' => Reports::statusList
        ];
    }

    /**
     *  Load CSV report
     * @param $file
     */
    public function actionLoadReport($file)
    {
        $fileReader = new FileReader($file);
        $fileReader->getReadyCSV();
    }

}


