<?php

namespace api\components\web;

use yii\web\Controller;
use api\components\rbac\AdminAccess;
use api\components\services\ControllerModelErrorTrait;

class AdminApiController extends Controller
{
    use ControllerModelErrorTrait;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AdminAccess::className()
            ]
        ];
    }

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }
}