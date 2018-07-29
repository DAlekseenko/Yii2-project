<?php
namespace frontend\controllersAbstract;

use yii;
use yii\filters\AccessControl;
use frontend\models\virtual\RechargeBalanceForm;
use common\models\AssistTransactions;
use common\models\PaymentTransactions;
use common\models\Users;
use common\components\services\Dictionary;

class UserController extends AbstractController
{

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
		];
	}

	public function actionRecharge()
	{
		/** @var \common\components\services\Environment $env */
		$env = Yii::$app->environment;
		$env->setName($env::MODULE_WEB);

		$model = new RechargeBalanceForm();
		$request = yii::$app->request;

		if ($request->isPost && $model->load($request->post()) && $model->validate() && $model->save(AssistTransactions::TYPE_RECHARGE, null, $env)) {
			$model->prepare();
			yii::info('User try to increase balance:');
		}

		return $request->isAjax ? $this->renderPartial('//partial/user/_recharge-form', ['model' => $model]) : $this->render('//partial/user/recharge', ['model' => $model]);
	}

	public function actionRechargeDialog($key)
	{
		/** @var \common\components\services\Environment $env */
		$env = Yii::$app->environment;
		$env->setName($env::MODULE_WEB);

		$transaction = PaymentTransactions::find()->currentUser()->byKey($key)->one();
		if (empty($transaction)) {
			throw new yii\web\NotFoundHttpException();
		}

		$balance = Yii::$app->user->identity->getBalance();  //Текущий баланс
		$totalSum = $transaction->sum;		 				 //Сумма платежа
		$need = $totalSum + Users::BALANCE_ALLOWED_REMAIN;	 //Сколько необходимо иметь на балансе средств для оплаты
		$difference = $need - $balance;						 //Разница между балансом и необходимой суммой

		/** Если необходима сумма большая,
		 * чем возможная сумма для пополнения счета одной транзакцией,
		 * то выводим ошибку */
		if ($difference > RechargeBalanceForm::MAX_PAY) {
			Yii::$app->session->setFlash('payErrorMessage', Dictionary::insufficientFunds());

			/** Добавляем форму для пополнения баланса */
		} else {
			$rechargeModel = new RechargeBalanceForm();
			if (
				Yii::$app->request->isPost &&
				$rechargeModel->load(Yii::$app->request->post()) &&
				$rechargeModel->validate() &&
				$rechargeModel->save(AssistTransactions::TYPE_PAYMENT, [$transaction->id], $env)
			) {
				$rechargeModel->prepare();
			}

			$params = [
				'res' => Users::BALANCE_ALLOWED_REMAIN,
				'sum' => $totalSum,
				'need' => $difference,
				'rechargeModel' => $rechargeModel,
				'validBalance' => $balance > Users::BALANCE_ALLOWED_REMAIN
			];
			$form = $this->renderPartial('//partial/invoices/_recharge-balance-form', $params);
		}
		return $this->render('//partial/user/recharge-dialog', ['transaction' => $transaction, 'form' => empty($form) ? '' : $form]);
	}

	public function actionRechargeOk()
	{
		/** @var AssistTransactions $assistTransaction */
		$assistTransaction = AssistTransactions::find()->where(['user_id' => yii::$app->user->identity->user_id, 'order_number' => yii::$app->request->get('ordernumber')])->one();
		if (empty($assistTransaction)) {
			throw new yii\web\NotFoundHttpException();
		}
		return $this->render('recharge-status', ['at' => $assistTransaction, 'statusOk' => true, 'transactions' => $assistTransaction->getTransactions()]);
	}

	public function actionRechargeNo()
	{
		$assistTransaction = AssistTransactions::find()->where(['user_id' => yii::$app->user->identity->user_id, 'order_number' => yii::$app->request->get('ordernumber')])->one();
		if (empty($assistTransaction)) {
			throw new yii\web\NotFoundHttpException();
		}
		return $this->render('recharge-status', ['at' => $assistTransaction, 'transactions' => []]);
	}
}
