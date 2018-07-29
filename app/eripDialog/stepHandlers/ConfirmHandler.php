<?php

namespace eripDialog\stepHandlers;

use api\components\services\Subscription\SubscriberHandler;
use common\models\Categories;
use common\models\Limits;
use common\models\LimitsGroup;
use eripDialog\exceptions\EdCategoryLimitException;
use eripDialog\exceptions\EdDayLimitException;
use eripDialog\exceptions\EdMonthLimitException;
use Yii;
use yii\web\NotFoundHttpException;
use common\models\Users;
use api\models\exceptions\ModelException;
use api\models\virtual\CodeLogin;
use frontend\models\virtual\RechargeBalanceForm;
use common\components\services\ModelManager;
use common\components\services\Environment;
use common\components\services\TemplateHelper;
use api\components\services\MtsProcessing\ProcessingCaller;
use common\components\services\Dictionary;
use common\models\ServicesLists;

class ConfirmHandler extends AbstractHandler
{
	protected $redirectUrl = '';

	protected $advanced = ['direct_pay' => true];

	protected $isGuestMode = false;

	/**
	 * @return bool
	 * @throws ModelException
	 * @throws NotFoundHttpException
	 * @throws \Exception
	 * @throws \Throwable
	 */
	public function beforeAction()
	{
		/** @var \common\components\services\Environment $environment */
		$environment = Yii::$app->environment;
		if (empty($this->transaction)) {
			throw new NotFoundHttpException('Transaction was not found');
		}
		$this->authorizeUser();

		$this->validateLimits($this->transaction->sum);

		if ($this->transaction->sumValidate() === false) {
			$this->response->setError($this->transaction->getFirstError('sum'));
			return false;
		}

		// Если карты разрешены для модуля.
		$module = $environment->getName();
		if ($module == Environment::MODULE_WEB || ($module == Environment::MODULE_APP && \yii::$app->request->get('version') >= 1.2)) {
			if (!$this->isGuestMode) {
				$rechargeBalanceForm = new RechargeBalanceForm();
				$modelManager = new ModelManager($rechargeBalanceForm);
				$canPay = $rechargeBalanceForm->canPayTransactions($this->user, [$this->transaction]);
				if ($canPay === false) {
					$this->response->setError($rechargeBalanceForm->getFirstError('sum'));
					return false;
				}
				$teaser = $rechargeBalanceForm->getRechargeTeaser();
				if (!empty($teaser)) {
					$this->advanced = [
						'direct_pay' => false,
						'model' => empty($teaser) ? [] : [
							'fields' => $modelManager->getFieldProperties()
						],
						'teaser' => $teaser
					];
					$this->transaction->setOnPayEnvironment($environment)->save();

					/** @todo Добавить url-builder и вынести логику из Caller. */
					$this->redirectUrl = Yii::$app->params['paymentRechargeUrl']['url'] . '?' .
						http_build_query(TemplateHelper::fillTemplates(
							Yii::$app->params['paymentRechargeUrl']['get'],
							['key' => $this->transaction->getTransactionKey()]
						));
					return false;
				}
				return true;
			}
		}

		if (!$this->user->canPay($this->transaction->sum, $text)) {
			$this->response->setError($text);
			return false;
		}
		return true;
	}

	public function afterAction()
	{
		/** @var \common\components\services\Environment $environment */
		$environment = Yii::$app->environment;

		if (!$this->user->canPay($this->transaction->sum, $text)) {
			$this->response->setError($text);
			return false;
		}
		$this->transaction->updateNewTransactionOnConfirm($this->user->user_id);
		$this->transaction->setOnPayEnvironment($environment, false);

		if ($this->transaction->save() === false) {
			$this->response->setError('Внутренняя ошибка сервера');
			return false;
		}
		$invoice = $this->transaction->invoice;
		if (isset($invoice)) {
			$invoice->payment_date = date('Y-m-d H:i:s', time());
			$invoice->save();
		}

		$processingCaller = new ProcessingCaller(
			MC_PAYMENT_PRODUCT,
			$this->transaction->user->phone,
			$this->transaction->uuid,
			$this->transaction->sum,
			$this->transaction->user->subscriber_uuid
		);
		if ($this->transaction->service->isInList([ServicesLists::LIST_ACCEPT_SERVICES])) {
			$sms = Dictionary::acceptSms(['service' => $this->transaction->service->name, 'sum' => $this->transaction->sum]);
			$processingCaller->setAccept($sms);
		}
		$subscriberHandler = SubscriberHandler::createByUser($this->transaction->user);
		if ($subscriberHandler->isSubscriptionEnable() && $this->transaction->isSubscriptionNeeded()) {
			$processingCaller->setSubscriptionService(SUBSCRIPTION_SERVICE_NAME);
		}
		$processingCaller->call();

		$this->cache->clear();

		/** @todo Добавить url-builder и вынести логику из Caller. */
		$this->redirectUrl = Yii::$app->params['paymentResultUrl']['url'] . '?' .
			http_build_query(TemplateHelper::fillTemplates(
				Yii::$app->params['paymentResultUrl']['get'],
				['id' => $this->transaction->getTransactionKey()]
			));

		return true;
	}

	public function prepareClientOutput()
	{
		$response = $this->response;
		if ($response->hasErrors()) {
			return $response->get();
		}

		return [
			'success' => true,
			'key' => $this->transaction->getTransactionKey(),
			'uuid' => $this->transaction->uuid,
			'redirect_url' => $this->redirectUrl,
			'advanced' => $this->advanced,
		];
	}
	protected function getNextMode()
	{
		return null;
	}

	/**
	 * @param $sum
	 * @throws EdDayLimitException
	 * @throws EdMonthLimitException
	 * @throws \Exception
	 */
	private function validateLimits($sum)
	{
		/** @var \api\components\services\ParamsService\ParamsService $paramsService */
		$paramsService = \yii::$app->{SERVICE_PARAMS};
		$this->validateCalendarLimits($sum, $paramsService->getMonthLimit(), $paramsService->getDayLimit());
		$this->validateCategoryLimits($sum, $paramsService->getCategoryLimits());
	}

	/**
	 * @param $sum
	 * @param $maxMonth
	 * @param $maxDay
	 * @return bool
	 * @throws EdDayLimitException
	 * @throws EdMonthLimitException
	 */
	private function validateCalendarLimits($sum, $maxMonth, $maxDay)
	{
		$limits = Limits::find()->byUserId($this->transaction->user_id)->one();
		if (empty($limits)) {
			return true;
		}
		if ( $sum + $limits->day_sum > $maxDay ) {
			throw (new EdDayLimitException())->setAvailableSum(max($maxDay - $limits->day_sum, 0));
		}
		if ( $sum + $limits->month_sum + $limits->day_sum > $maxMonth ) {
			throw (new EdMonthLimitException())->setAvailableSum(max($maxMonth - ($limits->month_sum + $limits->day_sum), 0));
		}
		return true;
	}

	/**
	 * @param  $sum
	 * @param  array $categoryLimits
	 * @return bool
	 * @throws EdCategoryLimitException
	 */
	private function validateCategoryLimits($sum, array $categoryLimits)
	{
		$existCategorySums = LimitsGroup::getUserCategorySums($this->transaction->user_id);
		try {
			$categoriesChain = \yii\helpers\ArrayHelper::getColumn($this->transaction->service->category->getParents(true), 'key');
		} catch (\Exception $e) {
			return true;
		}

		foreach ($categoryLimits as $key => $limitSum) {
			//Если сервис не содержит категорию, на которую наложены ограничения.
			if (!in_array($key, $categoriesChain)) {
				return true;
			}
			$userLimit = isset($existCategorySums[$key]) ? $sum + $existCategorySums[$key] : $sum;

			if ($userLimit > $limitSum) {
				$exception = new EdCategoryLimitException();
				$exception->setAvailableSum(max($limitSum - ($userLimit - $sum), 0));
				$exception->setCategoryName(Categories::findByUkey($key)->name);

				throw $exception;
			}
		}
		return true;
	}

	/**
	 * Пытаемся авторизовать пользователя
	 *
	 * @throws ModelException
	 * @throws \Exception
	 * @throws \Throwable
	 */
	private function authorizeUser()
	{
		if (!isset($this->user)) {
			$model = new CodeLogin();
			$model->phone = $this->request->get()['phone'];
			$model->password = $this->request->get()['password'];
			if ($model->validate() == false) {
				throw new ModelException($model);
			}
			$this->user = Users::getRealUser($model->phone);
			Yii::$app->session->set('userIdShowAuthButton', $this->user->user_id);
			$this->isGuestMode = true;
		}
	}
}
