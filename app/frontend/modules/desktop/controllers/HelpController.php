<?php
namespace frontend\modules\desktop\controllers;

use frontend\modules\desktop\models\virtual\OnlineHelp;
use yii;

class HelpController extends \frontend\controllersAbstract\HelpController
{
	/*public function actionFaq()
	{
		return $this->render('faq');
	}

	public function actionUssd()
	{
		return $this->render('ussd');
	}

	public function actionHowToConnect()
	{
		return $this->render('howToConnect');
	}

	public function actionTarif()
	{
		return $this->render('tarif');
	}

	public function actionLegalInformation()
	{
		return $this->render('legalInformation');
	}

	public function actionOnline()
	{
		$model = new OnlineHelp();

		if ($model->load(Yii::$app->request->post())) {
			if ($model->send()) {
				Yii::$app->session->setFlash('onlineSuccessMessage', 'Письмо отправлено');
			} else {
				Yii::$app->session->setFlash('onlineErrorMessage', 'Ошибка при отправке письма');
			}
		}

		return $this->render('online', ['model' => $model]);
	}*/
}
