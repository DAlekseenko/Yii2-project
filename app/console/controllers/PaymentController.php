<?php
/**
 * Created by PhpStorm.
 * User: kirsanov
 * Date: 21.06.2017
 * Time: 14:42
 */

namespace console\controllers;


use yii\console\Controller;
use common\models\PaymentTransactions;
use common\models\ServicesLists;
use common\models\InvoicesUsersData;
use common\models\Invoices;
use eripDialog\EdHelper;
use eripDialog\logMessages\EdPaymentLogMessage;
use eripDialog\logMessages\EdCancelLogMessage;
use common\components\services\PhoneService;
use common\components\services\NotificationService;
use console\models\CheckInvoices;
use common\components\services\MqJobMessage;
use common\components\services\MailService;

/**
 * Class PaymentController
 * @package console\controllers
 */
class PaymentController extends Controller
{
	/**
	 * @param $uuid
	 * @return bool
	 * @throws \Exception
	 * @throws \Throwable
	 */
	public function actionOnSuccess($uuid, $ttl = 5)
	{
		if (empty($tr = PaymentTransactions::find()->whereTransaction($uuid)->onlySuccess()->one())) {
			return false;
		}

		$paymentResult = $this->callEripConfirm($tr);
		if ($paymentResult === false) {
			if ($ttl) {
				/** @var \common\components\services\MqConnector $connector */
				$connector = \Yii::$app->amqp;
				$connector->sendMessageDirectly(new MqJobMessage(QUEUE_JOBS_DELAY_1MIN, 'payment/on-success', [$tr->uuid, --$ttl]));
			} else {
				/** @todo отправлять в Zabbix */
				MailService::sendAdminReportOnUnableConfirmTransaction($tr->getPaymentId());
			}
			return false;
		}
		$tr->erip_data = array_merge($tr->erip_data, ['eripResult' => $paymentResult]);
		// отправляем сообщение об успехе:
		PhoneService::sendPaymentSuccess($tr);

		// обновляем привязки и начисления:
		if ($tr->hasInvoice() && isset($tr->getFieldsMap()[0]['value'])) {
			$identifier = $tr->getFieldsMap()[0]['value'];

			if (!$tr->service->isInList([ServicesLists::LIST_WITH_EMPTY_INVOICES, ServicesLists::LIST_WITH_ONE_OFF_INVOICE])) {
				InvoicesUsersData::createInvoiceData($tr->user_id, $tr->service_id, $identifier);
			}

			if ($tr->service->isInList([ServicesLists::LIST_WITH_ONE_OFF_INVOICE])) {
				$userData = InvoicesUsersData::getUserActiveItem($tr->user_id, $tr->service_id, $identifier);
				if (!empty($userData)) {
					$userData->delete();
				}
			}

			foreach (Invoices::findActiveInvoices($tr->service_id, $identifier) as $invoice) {
				$invoice->delete();
			}
		}
		return $tr->save();
	}

	/**
	 * @param PaymentTransactions $tr
	 * @return array|bool
	 */
	public function callEripConfirm(PaymentTransactions $tr)
	{
		$eripResult = EdHelper::eripPayment($tr, EdPaymentLogMessage::class);
		if (isset($eripResult['success']) && $eripResult['success'] === true) {
			return $eripResult;
		}
		return false;
	}


	/**
	 * @param $uuid
	 * @return bool
	 */
	public function actionOnFail($uuid)
	{
		if (empty($tr = PaymentTransactions::find()->whereTransaction($uuid)->onlyFail()->one())) {
			return false;
		}

		EdHelper::eripCancel($tr, EdCancelLogMessage::class);

		if (isset($tr->invoice) && isset($tr->getFieldsMap()[0]['value'])) {
			$identifier = $tr->getFieldsMap()[0]['value'];

			sleep(60 * 10); // Ждем 10мин в надежде, что в ЕРИП восстановится сумма для начисления.

			$userData = InvoicesUsersData::getUserActiveItem($tr->user_id, $tr->service_id, $identifier);
			if (empty($userData)) {
				return true;
			}

			$notifications = NotificationService::newInvoicesNotification();
			$checkInvoice = new CheckInvoices();

			$result = $checkInvoice->check($userData->user_id, $userData->service_id, $userData->identifier);

			if ($result instanceof PaymentTransactions) {
				Invoices::createInvoice($result, $userData);
				$notifications->addUserData($userData->user_id, [$result], true);
				$notifications->sendAll();
			}
		}

		return true;
	}
}
