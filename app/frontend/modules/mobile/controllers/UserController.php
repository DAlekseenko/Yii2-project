<?php
namespace frontend\modules\mobile\controllers;

use frontend\modules\mobile\components\behaviors\RenderLayout;
use frontend\modules\mobile\models\virtual\EndRegistration;
use yii;

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

	public function actionEndRegistration()
	{
		$model = new EndRegistration();
		if ($model->load(Yii::$app->request->post()) && $model->changeUserInfo()) {
			$this->redirect(['/invoices']);
		}
		return $this->render('endRegistration', ['model' => $model]);
	}
}