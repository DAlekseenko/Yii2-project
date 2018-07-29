<?php
namespace frontend\controllersAbstract;

use api\components\services\Subscription\RegistrationService;
use common\components\web\ErrorAction;
use common\models\virtual\CaptchaRegistration;
use frontend\models\Users;
use frontend\models\virtual\SendPasswordSuccessForm;
use yii;
use common\models\virtual\Login;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

abstract class SiteController extends AbstractController
{
	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::className(),
				'only' => ['logout'],
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
					'logout' => ['post'],
				],
			],
		];
	}

	public function actions()
	{
		return [
			'captcha' => [
				'class' => 'yii\captcha\CaptchaAction',
			],
			'error' => [
				'class' => ErrorAction::class,
			],
		];
	}

	public function actionLogout()
	{
		Yii::$app->user->logout();

		return $this->redirect('/');
	}

	/**
	 * @param Login|SendPasswordSuccessForm $model
	 * @return bool|yii\web\Response
	 */
	private function login(&$model)
	{
		if (!\Yii::$app->user->isGuest) {
			return $this->goHome();
		}

		if ($model->load(Yii::$app->request->post()) && $model->login()) {
			$this->redirect(Yii::$app->user->identity->password ? '/invoices' : '/user/end-registration');
			return true;
		}
		return false;
	}

	public function actionLogin()
	{
		$model = new Login();
		if (!$this->login($model)) {
			return $this->renderPartial('//partial/site/login', [
				'loginFormModel' => $model,
			]);
		}
		return '';
	}

	//срабатывает при нажатии на кнопку Аворизоваться на странице платежа после оплаты(при неавторизованном пользователе)
	public function actionLoginByClick()
	{
		$userIdLoginByClick = Yii::$app->session->remove('userIdLoginByClick');
		if ($userIdLoginByClick) {
			$model = Users::findOne($userIdLoginByClick);
			if ($model && Yii::$app->user->login($model, 60 * 10)) {
				return $this->redirect(Yii::$app->user->identity->password ? '/invoices' : '/user/end-registration');
			}
		}
		return $this->goHome();
	}

	public function actionSendPasswordSuccess($new = 1)
	{
		$model = new SendPasswordSuccessForm();
		if (!$this->login($model)) {
			return $this->renderAjax('//partial/site/sendPasswordSuccess', ['loginFormModel' => $model, 'isNew' => (bool) $new]);
		}
		return '';
	}

	public function actionSendPassword($new = 1)
	{
		$model = new CaptchaRegistration();
		$registrationService = new RegistrationService($model);

		if (Yii::$app->request->isPost) {
			if ($registrationService->validate(Yii::$app->request->post()) === false) {
				return $this->renderForm($model, $new);
			}
			/** @var \common\components\services\Environment $environment */
			$environment = Yii::$app->environment;
			$user = $registrationService->registerUser($environment);
			if ($user === false) {
				return $this->renderForm($model, $new);
			}
			if ($registrationService->sendRegistrationCode($user) === false) {
				return $this->renderForm($model, $new);
			}
			$loginForm = new SendPasswordSuccessForm();
			$loginForm->setAttributes($model->getAttributes());
				return $this->renderAjax('//partial/site/sendPasswordSuccess', ['loginFormModel' => $loginForm, 'isNew' => (bool) $new]);
		}
		return $this->renderForm($model, $new);
	}

	private function renderForm($model, $isNew)
	{
		return $this->renderAjax('//partial/site/sendPassword', ['smsSenderModel' => $model, 'isNew' => (bool) $isNew]);
	}
}