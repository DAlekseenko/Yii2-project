<?php

namespace api\controllersAdmin;

use api\components\web\AdminApiController;
use api\models\admin\Users;
use common\models\AuthItem;
use yii;
use yii\web\HttpException;
use yii\filters\AccessControl;
use api\models\admin\virtual\Login;
use yii\helpers\ArrayHelper;

class UserController extends AdminApiController
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login'],
                        'allow' => true,
                    ],
                    [
                        'roles' => array_keys(Yii::$app->authManager->getRoles()),
                        'allow' => true,
                    ]
                ],
            ],
        ];
    }

    /**
     * User profile.
     * @return mixed
     */
    public function actionGet()
    {
        /** @var Users $user */
        $user = yii::$app->user->identity;
        $user::$serializeMode = $user::SERIALIZE_MODE_WITH_ACCESS;

        return $user;
    }

    /**
     * User login
     * @return mixed
     * @throws HttpException if the model cannot be found
     */
    public function actionLogin()
    {
        $model = new Login();
        if (!$model->load(Yii::$app->request->post(), '')) {
            throw new HttpException(400);
        }
        if ($model->login()) {
            $user = $model->getUser();
            $user::$serializeMode = $user::SERIALIZE_MODE_WITH_ACCESS;
            return [
                'user' => $user,
                'authKey' => $user->getCryptAuthData()
            ];
        }
        if ($model->hasErrors()) {
            return $this->returnFieldError($model);
        }
        return false;
    }
}
