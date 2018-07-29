<?php
namespace frontend\controllersAbstract;

use api\components\services\Subscription\SubscriberHandler;
use common\components\services\Dictionary;
use common\models\AssistTransactions;
use common\models\Categories;
use common\models\Invoices;
use common\models\Services;
use frontend\components\behaviors\AjaxEmptyLayout;
use frontend\models\InvoicesIgnoreDefault;
use frontend\models\virtual\InvoicesMobileForm;
use frontend\models\InvoicesUsersData;
use yii;
use yii\filters\AccessControl;
use frontend\models\virtual\RechargeBalanceForm;
use common\models\Users;

class InvoicesController extends AbstractController
{

	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::className(),
				'rules' => [
					[
						'roles' => [Users::ROLE_BANNED],
						'allow' => false,
					],
					[
						'allow' => true,
						'roles' => ['@'],
					],
					[
						'allow' => false,
					],
				],
			],
			'ajaxEmptyLayout' => [
				'class' => AjaxEmptyLayout::className(),
			],
		];
	}

	public function actionIndex()
	{
		return $this->render('index', [
			'unpaidInvoices' => $this->actionUnpaidInvoices()
		]);
	}

	public function actionUnpaidInvoices()
	{
		$unpaidInvoices = Invoices::findActive()->withService()->with('transaction')->all();

		return $this->renderPartial('_unpaidInvoices', ['unpaidInvoices' => $unpaidInvoices]);
	}

	public function actionUpdateUserInvoice()
	{
		$model = InvoicesUsersData::find()->currentUser()->byId((int)Yii::$app->request->post('id', Yii::$app->request->get('id')))->one();
		if (!$model) {
			throw new yii\web\BadRequestHttpException('Bad id');
		}

		$service = Services::findById($model['service_id'], true);
		$category = Categories::findById($service['category_id']);
		$globalCategory = Categories::getGlobalByCategoryId($service['category_id']);
		return $this->render('invoicesUsersDataUpdate', ['model' => $model, 'service' => $service, 'category' => $category, 'globalCategory' => $globalCategory]);
	}

	public function actionIgnoreInvoiceFolder($key)
	{
		$model = InvoicesIgnoreDefault::find()->where(['key' => $key])->one() ?: new InvoicesIgnoreDefault();
		$model->key = $key;
		if ($model->save()) {
			return '';
		}
		Yii::$app->response->setStatusCode(400);
		return implode(' ', $model->getFirstErrors());
	}

	protected function payInvoice(InvoicesMobileForm $invoicesMobileForm)
	{
		/** @var Users $user */
		$user = Yii::$app->user->identity;
		$subscriberHandler = SubscriberHandler::createByUser($user);
		$info = $subscriberHandler->getUserSubscriptionInfo();

		$invoices = $invoicesMobileForm->getInvoices();
		if (empty($invoices)) {
			Yii::$app->session->setFlash('payErrorMessage', 'Начисления не найдены.');
		} elseif (Yii::$app->request->post('actionPay')) {
			if ($ids = $invoicesMobileForm->pay()) {
				return $this->redirect(['/payments/history-items', 'ids' => $ids]);
			}
			Yii::$app->session->setFlash('payErrorMessage', 'Произошла ошибка. Попробуйте позже.');
		}
		return $this->render('//partial/invoices/pay-invoices', [
			'transactions' => $invoicesMobileForm->getTransactions(),
			'form' => $this->renderPartial('//partial/invoices/_pay-invoices-form', ['subscriptionInfo' => $info])
		]);
	}

	protected function payInvoicePrepare(InvoicesMobileForm $invoicesMobileForm)
	{
		/** @var \common\components\services\Environment $env */
		$env = Yii::$app->environment;
		$env->setName($env::MODULE_WEB);

		/** @var Users $user */
		$user = Yii::$app->user->identity;
		$balance = $user->getBalance();  //Текущий баланс
		$totalSum = $invoicesMobileForm->getTotalSum();		 //Сумма платежа
		$need = $totalSum + Users::BALANCE_ALLOWED_REMAIN;	 //Сколько необходимо иметь на балансе средств для оплаты
		$difference = $need - $balance;						 //Разница между балансом и необходимой суммой

		$subscriberHandler = SubscriberHandler::createByUser($user);
		$info = $subscriberHandler->getUserSubscriptionInfo();

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
				$rechargeModel->save(AssistTransactions::TYPE_INVOICE, $invoicesMobileForm->transactionIds, $env)
			) {
				$invoicesMobileForm->setEnvironment(Yii::$app->environment);
				$rechargeModel->prepare();
			}

			$params = [
				'res' => Users::BALANCE_ALLOWED_REMAIN,
				'sum' => $totalSum,
				'need' => $difference,
				'rechargeModel' => $rechargeModel,
				'validBalance' => $balance > Users::BALANCE_ALLOWED_REMAIN,
				'subscriptionInfo' => $info
			];
			$form = $this->renderPartial('//partial/invoices/_recharge-balance-form', $params);
		}
		return $this->render('//partial/invoices/pay-invoices', [
			'transactions' => $invoicesMobileForm->getTransactions(),
			'form' => empty($form) ? '' : $form
		]);
	}

	public function actionPayInvoicesMobile()
	{
		$model = new InvoicesMobileForm(['ids' => Yii::$app->request->post('ids', Yii::$app->request->get('ids'))]);

		/** Если не прошла валидация, значит недостаточно средств для оплаты, выведем приглашение на пополнение счета с карты */
		if ($model->validate()) {
			return $this->payInvoice($model);
		}
		return $this->payInvoicePrepare($model);
	}
}
