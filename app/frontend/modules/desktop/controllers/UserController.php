<?php
namespace frontend\modules\desktop\controllers;

use frontend\modules\desktop\components\behaviors\RenderLayout;
use frontend\models\virtual\EndRegistration;
use yii;
use frontend\models\virtual\ChangeUserInfo;

/**
 * @mixin RenderLayout
 */
class UserController extends \frontend\controllersAbstract\UserController
{

	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['renderLayout'] = [
			'class' => RenderLayout::className(),
		];
		return $behaviors;
	}

	public function actionChangeUserInfo()
	{
		Yii::$app->response->format = yii\web\Response::FORMAT_JSON;

		$model = new ChangeUserInfo();
		if ($model->load(Yii::$app->request->post())) {
			if ($model->changeUserInfo()) {
				$user = Yii::$app->user->identity;
				return [
					'status' => 'success',
					'userNameFull' => $user->getUserNameFull(),
					'firstName' => $user->first_name,
					'lastName' => $user->last_name,
					'patronymic' => $user->patronymic,
				];
			}
		} else {
			$model->setAttributes(Yii::$app->user->identity->getAttributes());
		}

		return [
			'content' => $this->renderAjax('changeUserInfo', ['model' => $model]),
		];
	}

	public function actionEndRegistration()
	{
		$model = new EndRegistration();
		if ($model->load(Yii::$app->request->post()) && $model->changeUserInfo()) {
			$this->redirect(['/invoices']);
		}
		return $this->render('endRegistration', ['model' => $model]);
	}
}