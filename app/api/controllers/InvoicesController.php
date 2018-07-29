<?php
namespace api\controllers;

use yii;
use api\components\formatters\EntitiesFormatter;
use api\components\services\Subscription\SubscriberHandler;
use common\components\services\Dictionary;
use common\components\services\Helper;
use common\components\services\PhoneService;
use common\models\PaymentTransactions;
use common\models\Invoices;
use common\models\Slider;
use common\models\Users;
use common\models\virtual\ApiRegistration;
use api\components\services\Subscription\RegistrationService;
use console\models\CheckInvoices;
use eripDialog\EdApplication;
use eripDialog\exceptions\EdEmptyInvoiceServiceException;
use eripDialog\exceptions\EdMultipleFieldsServiceException;
use eripDialog\exceptions\EdStepException;
use eripDialog\exceptions\EdIncorrectInvoiceServiceException;
use frontend\models\InvoicesUsersData;
use frontend\models\InvoicesIgnoreDefault;
use common\components\services\MqJobMessage;
use eripDialog\exceptions\EdCategoryLimitException;
use eripDialog\exceptions\EdDayLimitException;
use eripDialog\exceptions\EdLimitException;
use eripDialog\exceptions\EdMonthLimitException;

class InvoicesController extends AbstractController
{
	public function behaviors()
	{
		return [
			'access' => [
				'class' => yii\filters\AccessControl::className(),
				'rules' => [
					[
						'actions' => ['get-by-uuid', 'get-by-msisdn', 'pay'],
						'ips' => ALLOWED_INTERNAL_IPS,
						'allow' => true,
					],
					[
						'roles' => [Users::ROLE_BANNED],
						'allow' => false,
					],
					[
						'roles' => ['@'],
						'allow' => true,
					],
					[
						'allow' => false,
					],
				],
			]
		];
	}

	public function actionClearUserInvoices()
	{
		return InvoicesUsersData::deleteAll(['user_id' => yii::$app->user->id, 'visible_type' => InvoicesUsersData::VISIBILITY_USER]);
	}

	public function actionGetSlider($location_id = null)
	{
		return ['items' => Slider::findActiveBanners($location_id)];
	}

	public function actionGetCurrent()
	{
		$invoices = $this->findCurrentInvoices()->andWhere(['user_id' => Yii::$app->user->id])->all();
		$result = [];

		foreach ($invoices as $invoice) {
			$result[] = EntitiesFormatter::invoiceFormatter($invoice);
		}
		return ['invoices' => $result];
	}

	public function actionGetUserData()
	{
		$list = InvoicesUsersData::getList(InvoicesIgnoreDefault::findIgnored());

		return ['data' => array_merge($list), 'count' => count($list)];
	}

	public function actionCreateUserData($service_id, $identifier, $description = '')
	{
		$invoicesUsersData = InvoicesUsersData::getNewInstance(yii::$app->user->id, $service_id, $identifier, $description);

		if ($invoicesUsersData->validate() === false) {
			return $this->returnFieldError($invoicesUsersData);
		}

		return $this->checkInvoice($invoicesUsersData);
	}

	public function actionEditUserData($id, $identifier, $description = '')
	{
		/** @var InvoicesUsersData $userData */
		$userData = InvoicesUsersData::find()->where(['id' => $id])->one();

		if (empty($userData)) {
			throw new yii\web\NotFoundHttpException('Привязка не найдена');
		}
		$newId = $identifier != $userData->identifier;

		$userData->identifier = $identifier;
		$userData->description = $description;

		if ($userData->validate() === false) {
			return $this->returnFieldError($userData);
		}
		if ($newId) {
			return $this->checkInvoice($userData);
		}
		if ($userData->save() === false) {
			return $this->returnFieldError($userData);
		}
		return $userData;
	}

	public function actionDeleteUserData($id)
	{
		$invoicesUsersData = InvoicesUsersData::find()->where(['id' => $id])->one();

		if (empty($invoicesUsersData)) {
			throw new yii\web\NotFoundHttpException('Привязка не найдена');
		}

		return $invoicesUsersData->setDeleted();
	}

	public function actionGetByUuid()
	{
		$uuid = Yii::$app->request->get('uuid');
		if (!$uuid) {
			throw new yii\web\BadRequestHttpException('Parameter uuid is required');
		}

		/** @var Invoices $invoice */
		$invoice = $this->findActiveInvoice()->andWhere(['uuid' => $uuid])->one();
		if (!$invoice) {
			throw new yii\web\NotFoundHttpException('Invoice was not found');
		}

		return [
			'uuid' => $invoice->uuid,
			'service' => $invoice->service->name,
			'category' => $invoice->service->category->name,
			'fields' => $invoice->transaction->getFieldsMap(),
			'sum' => Helper::prepareSum($invoice->getTotalSum()),
		];
	}

	public function actionGetByMsisdn($msisdn)
	{
		/** @var \common\components\services\Environment $env */
		$env = Yii::$app->environment;
		$env->setName($env::MODULE_USSD)->setProp();

		$model = new ApiRegistration();
		$registrationService = new RegistrationService($model);
		if ($registrationService->validate(['phone' => $msisdn]) === false) {
			throw new yii\web\NotFoundHttpException('Incorrect MSISDN');
		}
		$user = $registrationService->registerUser($env);
		if ($user === false) {
			throw new yii\web\NotFoundHttpException('User was not found');
		}
		if ($user->subscription_status == $user::USER_TYPE_BLANK) {
			$user->setStatusUser()->save();
		}

		/** @var Invoices[] $invoices */
		$invoices = $this->findActiveInvoice()->andWhere(['user_id' => $user->id])->all();
		$result = [];
		foreach ($invoices as $invoice) {
			$result[] = [
				'uuid' => $invoice->uuid,
				'service' => $invoice->service->name,
				'category' => $invoice->service->category->name,
				'fields' => $invoice->transaction->getFieldsMap(),
				'sum' => Helper::prepareSum($invoice->getTotalSum()),
			];
		}
		return $result;
	}

	/**
	 * Оплата начислений для мобильников.
	 *
	 * @param array $transaction_uuids
	 * @return array
	 * @throws yii\web\HttpException
	 */
	public function actionPayInvoice(array $transaction_uuids)
	{
		$transactions = PaymentTransactions::find()->whereTransaction($transaction_uuids)->onlyNew()->all();

		$totalSum = 0;
		foreach ($transactions as $transaction) {
			$totalSum += (float) $transaction->sum;
		}
		if (!yii::$app->user->identity->canPay($totalSum, $text)) {
			throw new yii\web\HttpException(402, $text);
		}

		$transactionsResult = [];
		foreach ($transactions as $transaction) {
			try {
				$this->pay($transaction);
				$transactionsResult[$transaction->uuid] = true;
			} catch (\Exception $e) {
				$transactionsResult[$transaction->uuid] = false;
			}
			/** @todo ставим таймаут для того, чтобы не отправлять одновременно транзакции */
			usleep(100000);
		}

		foreach ($transaction_uuids as $uuid) {
			if (!isset($transactionsResult[$uuid])) {
				$transactionsResult[$uuid] = false;
			}
		}

		if (array_sum($transactionsResult) == 0) {
			throw new yii\web\HttpException(503, 'Оплата временно недоступна, попробуйте позднее');
		}

		return [
			'transactions' => $transactionsResult
		];
	}

	/**
	 * Метод оплаты начисления через USSD
	 *
	 * @param  $uuid
	 * @return string
	 * @throws \Exception
	 * @throws yii\web\ServerErrorHttpException
	 */
	public function actionPay($uuid)
	{
		/** @var \common\components\services\Environment $env */
		$env = Yii::$app->environment;
		$env->setName($env::MODULE_USSD)->setProp();

		$transaction = PaymentTransactions::find()->whereTransaction($uuid)->onlyNew()->one();

		$subscriberHandler = SubscriberHandler::createByUser($transaction->user);
		if ($subscriberHandler->isSubscriptionRequired()) {
			PhoneService::sendAbstractSms($transaction->user->phone, Dictionary::ussdUnsubscribeUserSms());
			return '';
		}

		return $this->pay($transaction);
	}

	/**
	 * @param PaymentTransactions $paymentTransactions
	 * @return string
	 * @throws \Exception
	 * @throws \Throwable
	 * @throws yii\web\ServerErrorHttpException
	 */
	private function pay(PaymentTransactions $paymentTransactions)
	{
		/** @var \api\components\services\ParamsService\ParamsService $paramsService */
		$paramsService = \yii::$app->{SERVICE_PARAMS};
		try {
			$result = EdApplication::confirmPayment($paymentTransactions);

			if ($result->hasErrors()) {
				Yii::$app->response->setResponseMessage($result->getError());
				throw new yii\web\ServerErrorHttpException($result->getError());
			}
		} catch (EdLimitException $e) {
			if ($e instanceof EdMonthLimitException) {
				PhoneService::sendAbstractSms($paymentTransactions->user->phone, $paramsService->makeMonthLimitMsg($e->getAvailableSum()) );
			} elseif ($e instanceof EdDayLimitException) {
				PhoneService::sendAbstractSms($paymentTransactions->user->phone, $paramsService->makeDayLimitMsg($e->getAvailableSum()) );
			} elseif ($e instanceof EdCategoryLimitException) {
				PhoneService::sendAbstractSms($paymentTransactions->user->phone, $paramsService->makeCategoryLimitMsg($e->getAvailableSum(), $e->getCategoryName()) );
			}
		}

		return '';
	}

	/**
	 * @param InvoicesUsersData $userData
	 * @return array|InvoicesUsersData
	 * @throws EdStepException
	 * @throws \Exception
	 */
	private function checkInvoice(InvoicesUsersData $userData)
	{
		$checkResult = new CheckInvoices();
		$app = new EdApplication();
		try {
			$checkResult = $checkResult->runDialog($app, $userData->user_id, $userData->service_id, $userData->identifier);
			if ($checkResult instanceof PaymentTransactions) {
				if (Invoices::createInvoice($checkResult, $userData) instanceof Invoices) {
					/** @var \common\components\services\MqConnector $connector*/
					$connector = Yii::$app->amqp;
					$connector->sendMessageDirectly(new MqJobMessage(yii::$app->params['jobsQueue'], 'invoices/inform', [$checkResult->uuid]));
				} else {
					throw new \Exception($userData->isNewRecord ? 'Не удалось создать привязку' : 'Не удалось отредактировать привязку');
				}
			}
			if ($checkResult === 0 && $userData->save() === false) {
				return $this->returnFieldError($userData);
			}
			return $userData;

		} catch (EdStepException $e) {
			if ($e->getStep() == 2) {
				$userData->addError('identifier', $e->getMessage());
				return $this->returnFieldError($userData);
			}
			throw $e;
		} catch (EdMultipleFieldsServiceException $e) {
			return $this->saveAsNotInvoice($userData);
		} catch (EdEmptyInvoiceServiceException $e) {
			return $this->saveAsNotInvoice($userData);
		} catch (EdIncorrectInvoiceServiceException $e) {
			$userData->addError('service_id', 'У данного сервиса отсутствуют начисления');
			return $this->returnFieldError($userData);
		}
	}

	private function saveAsNotInvoice(InvoicesUsersData $userData)
	{
		$userData->is_invoice = false;
		if ($userData->save() === false) {
			throw new \Exception($userData->isNewRecord ? 'Не удалось создать привязку' : 'Не удалось отредактировать привязку');
		}

		return $userData;
	}

	private function findCurrentInvoices()
	{
		return Invoices::find()->withService()
					   ->with('transaction')
					   ->where(['or', ['>', 'payment_date', date('Y-m-d H:i:s', time() - 60 * 60)], 'payment_date IS NULL']);
	}

	/**@return \common\models\InvoicesQuery*/
	private function findActiveInvoice()
	{
		return Invoices::find()->withService()->with('transaction')->where('payment_date IS NULL');
	}
}
