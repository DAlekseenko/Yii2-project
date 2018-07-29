<?php

namespace frontend\controllersAbstract;

use common\models\Documents;
use frontend\models\virtual\ChangeUserInfo;
use frontend\models\virtual\SettingsChangePassword;
use yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\Users;
use api\components\services\Subscription\SubscriberHandler;

class SettingsController extends AbstractController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'change-password' => ['post'],
                ],
            ],
        ];
    }

	public function actionIndex()
	{
        /** @var Users $user */
        $user = yii::$app->user->identity;
        $changeUserInfoModel = new ChangeUserInfo();
		$changeUserInfoModel->setAttributes($user->getAttributes());
		return $this->render('//partial/settings/index',
        [
            'changePasswordModel' => new SettingsChangePassword(),
            'changeUserInfoModel' => $changeUserInfoModel,
            'document' => Documents::findByKey(Documents::KEY_RULES),
            'showSubscriptionTool' => $user->isSubscriber()
        ]);
	}

    public function actionChangePassword()
    {
        Yii::$app->response->format = yii\web\Response::FORMAT_JSON;

        $model = new SettingsChangePassword();
        if ($model->load(Yii::$app->request->post()) && $model->changePassword()) {
            return [
                'result' => 'success',
                'successMessage' => 'Пароль изменен'
            ];
        } else {
            return [
                'content' => $this->renderAjax('//partial/settings/changePassword', ['changePasswordModel' => $model])
            ];
        }
    }

    public function actionChangeUserInfo()
    {
        Yii::$app->response->format = yii\web\Response::FORMAT_JSON;

        $model = new ChangeUserInfo();
        if ($model->load(Yii::$app->request->post()) && $model->changeUserInfo()) {
            $user = Yii::$app->user->identity;
            return [
                'result' => 'success',
                'successMessage' => 'Данные изменены',
                'userNameFull' => $user->getUserNameFull(),
                'firstName' => $user->first_name,
                'lastName' => $user->last_name,
                'patronymic' => $user->patronymic
            ];
        } else {
            return [
                'content' => $this->renderAjax('changeUserInfo', ['changeUserInfoModel' => $model])
            ];
        }
    }
}
