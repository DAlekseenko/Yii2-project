<?php

namespace console\controllers;

use common\components\services\PhoneService;
use common\components\services\TemplateHelper;
use common\models\AssistTransactions;
use common\models\PaymentTransactions;
use common\models\Users;
use eripDialog\EdApplication;
use frontend\models\virtual\InvoicesMobileForm;
use PbrLibBelCommon\Caller\WsCaller;
use PbrLibBelCommon\Exceptions\CallerHttpException;
use PbrLibBelCommon\Exceptions\CallerInitException;
use yii;
use yii\console\Controller;
use yii\web\HttpException;
use common\components\services\MqAnswerMessage;
use eripDialog\EdLogger;
use common\models\Ussd;
use common\models\virtual\ApiRegistration;
use api\components\services\Subscription\RegistrationService;
use eripDialog\exceptions\EdCategoryLimitException;
use eripDialog\exceptions\EdDayLimitException;
use eripDialog\exceptions\EdLimitException;
use eripDialog\exceptions\EdMonthLimitException;

class HandlersController extends Controller
{
	const LOG = 'rest';
	const LOG_USSD = 'ussd';
	const USSD_CHARITY_COMMISSION = 0.04;

	protected $intervals = [
		60, 60, 180, 300, 600
	];

	public function actionMcCall($transaction_uuid)
	{
		Yii::info('Prepare MC call for transaction uuid: ' . $transaction_uuid, self::LOG);
		$transaction = PaymentTransactions::find()->whereTransaction($transaction_uuid)->one();
		if (empty($transaction)) {
			Yii::error('Transaction was not found', self::LOG);
			return false;
		}
        $caller = new WsCaller(Yii::$app->params['McPaymentUrl']['url']);
        $caller->bulkSetGetParameters(
            TemplateHelper::fillTemplates(
                Yii::$app->params['McPaymentUrl']['get'],
                [
                    'phone' => $transaction->user->phone,
                    'price' => $transaction->sum * 1000000, /** @todo временный костыль для МК */
                    'guid' => $transaction->uuid,
                ]
            )
        );

		$iteration = 0;
		while (isset($this->intervals[$iteration])) {
			try {
				Yii::info('MC call iteration: ' . ($iteration + 1), self::LOG);
				$uuid = trim($caller->call());
				if ($uuid != $transaction->uuid) {
					throw new HttpException(404, 'MC returns an empty result');
				}
				Yii::info('MC call successfully!', self::LOG);
				return true;
			} catch (CallerInitException $e) {
                Yii::error('Error building request: ' . $e->getMessage(), self::LOG);
                break;
            } catch (CallerHttpException $e) {
                Yii::warning('Unable to call MC: ' . $e->statusCode . ' ' . $e->getName(), self::LOG);
                sleep($this->intervals[$iteration++]);
            } catch (\Exception $e) {
                Yii::error('Unexpected error: ' . $e->getMessage(), self::LOG);
                break;
            }
		}
		Yii::warning('MC call unsuccessfully!', self::LOG);

		return false;
	}

	/**
	 * Действие, которое выполняется на событие подтверждения успеха транзикции банком.
	 * @param string $orderNumber
	 * @return bool
	 * @throws \Exception
	 * @throws \Throwable
	 */
	public function actionAssistTransactionProcess($orderNumber)
	{
		/** @var AssistTransactions $assistTransaction */
		$assistTransaction = AssistTransactions::findOne(['order_number' => $orderNumber]);
		if (empty($assistTransaction)) {
			Yii::error('Assist transaction was not found (order_number: ' . $orderNumber . ')');
			return false;
		}
		switch ($assistTransaction->type) {
			case AssistTransactions::TYPE_RECHARGE:
				Yii::info('Assist result for recharge transaction (order_number: ' . $orderNumber . ')');
				break;
			case AssistTransactions::TYPE_INVOICE:
				Yii::info('Assist result for invoice transaction (order_number: ' . $orderNumber . ')');
				$invoiceMobileForm = new InvoicesMobileForm(['transactionIds' => $assistTransaction->data, 'userId' => $assistTransaction->user_id]);
				$user = $assistTransaction->user;
				if ($this->assistBalanceWaiter($assistTransaction, function() use ($invoiceMobileForm, $user) {
					$user->clearBalanceCache();
					return $invoiceMobileForm->validate();
				})) {
					foreach ($invoiceMobileForm->getInvoices() as $invoice) {
						$payResult = $invoiceMobileForm->payInvoice($invoice);
						Yii::info('Pay invoice result: ' . (int) $payResult . ' (order_number: ' . $orderNumber . ')');
					}
				} else {
					Yii::error('Cant pay invoice: insufficient funds (order_number: ' . $orderNumber . ')');
				}
				break;
			case AssistTransactions::TYPE_PAYMENT:
				/** @var \api\components\services\ParamsService\ParamsService $paramsService */
				$paramsService = \yii::$app->{SERVICE_PARAMS};

				Yii::info('Assist result for payment transaction (order_number: ' . $orderNumber . ')');
				$transaction = PaymentTransactions::find()->onlyNew()->andWhere(['id' => $assistTransaction->data[0], 'user_id' => $assistTransaction->user_id])->one();
				$user = $assistTransaction->user;
				if ($this->assistBalanceWaiter($assistTransaction, function() use ($user, $transaction) {
					return $user->clearBalanceCache()->canPay($transaction->sum, $text);
				})) {
					$payResult = 0;
					try {
						$payResult = EdApplication::confirmPayment($transaction)->hasErrors();
					} catch (EdLimitException $e) {
						if ($e instanceof EdMonthLimitException) {
							PhoneService::sendAbstractSms($user->phone, $paramsService->makeMonthLimitMsg($e->getAvailableSum()) );
						} elseif ($e instanceof EdDayLimitException) {
							PhoneService::sendAbstractSms($user->phone, $paramsService->makeDayLimitMsg($e->getAvailableSum()) );
						} elseif ($e instanceof EdCategoryLimitException) {
							PhoneService::sendAbstractSms($user->phone, $paramsService->makeCategoryLimitMsg($e->getAvailableSum(), $e->getCategoryName()) );
						}
					}
					Yii::info('Pay transaction result: ' . (int) $payResult . ' (order_number: ' . $orderNumber . ')');
				} else {
					Yii::error('Cant pay transaction: insufficient funds (order_number: ' . $orderNumber . ')');
				}
				break;
		}
		return true;
	}

	private function assistBalanceWaiter(AssistTransactions $at, callable $constrain)
	{
		for ($i = 0; $i < 39; $i++) {
			if ($constrain()) {
				return true;
			}
			Yii::info('Not ready to pay order: ' . $at->order_number);
			sleep($i < 30 ? 1 : 30);
		}
		return false;
	}

    /**
     * Оплата благотворительности.
     *
     * @param $phone
     * @param $plug
     * @param $code
     * @return bool
     * @throws \Exception
     */
	public function actionPayByPhone($phone, $plug, $code)
	{
		/** @var Ussd $ussd */
		$ussd = Ussd::find()->where(['plug' => $plug, 'code' => $code])->orWhere(['plug' => $plug, 'code' => '*'])->one();
		if (empty($ussd)) {
			PhoneService::sendAbstractSms($phone, 'Сервис не найден');
		}

		/** @var \common\components\services\Environment $env */
		$env = Yii::$app->environment;
		$env->setName($env::MODULE_USSD)->setProp(['target' => 'charity', 'module' => $ussd::PLUG_NAMES[$ussd->plug], 'code' => $ussd->code]);

		$model = new ApiRegistration();
		$registrationService = new RegistrationService($model);
		if ($registrationService->validate(['phone' => $phone]) === false) {
			throw new yii\web\NotFoundHttpException('Incorrect MSISDN');
		}
		$user = $registrationService->registerUser($env);
		if ($user === false) {
			PhoneService::sendAbstractSms($phone, 'Данная операция не может быть выполнена');
			throw new yii\web\NotFoundHttpException('User was not found');
		}

		if (!$user->canPay(bcadd($ussd->sum, self::USSD_CHARITY_COMMISSION, 2), $text)) {
			PhoneService::sendAbstractSms($phone, $text);
			return false;
		}

		$app = new EdApplication();
		$logger = new EdLogger('backgroundDialog', 'user_dialog');
		$app->setLogger($logger);
		$app->setUser($user);
		$app->setOnTransactionCreate(function(PaymentTransactions $tr) use ($ussd) {
			$tr->setCustomSuccessMsg($ussd->success_sms_text, MC_SEND_SMS_PRODUCT_PAY)->setUnsubscriptionMode();
		});

		$app->backgroundRun($ussd->service_id, $ussd->sum, $ussd->fields);
		$response = $app->getResponse();
		if ($response->hasErrors()) {
			PhoneService::sendAbstractSms($phone, $response->getError());
		}
	}

	/** @noinspection MoreThanThreeArgumentsInspection
	 * @param $method
	 * @param $answerTo
	 * @param $connectionId
	 * @param $requestId
	 * @param $params
	 */
	public function actionSubProcess($method, $answerTo, $connectionId, $requestId, $params)
	{
		$apiAnswer = $this->runApiApp($method, $params);
		Yii::info('Answer for requestId '.$requestId.':', 'rest');
		Yii::info($apiAnswer, 'rest');

		/** @var \common\components\services\MqConnector $connector*/
		$connector = Yii::$app->amqp;
		$queueManager = $connector->getConnectionManager();
		$answerMessage = new MqAnswerMessage($connectionId, $answerTo, $requestId);
		$answerMessage->setContent($apiAnswer);
		$queueManager->sendSimple($answerMessage);
	}

	/**
	 * @param $method
	 * @param $params
	 * @return mixed
	 */
	private function runApiApp($method, $params)
	{
		Yii::info('Running API app for method '.$method.' with params '.$params, 'rest');
		$command = 'php ' . ROOT_DIR . 'run-web-api.php ' . $method . ' ' . escapeshellarg($params);
		try {
			return json_decode(`$command`, 1);
		}
		catch (\Exception $e) {
			Yii::error($e, 'rest');
		}
	}
}
