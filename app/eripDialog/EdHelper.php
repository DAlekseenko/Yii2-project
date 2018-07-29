<?php

namespace eripDialog;

use common\components\services\MailService;
use PbrLibBelCommon\Caller\WsCaller;
use yii;
use common\models\PaymentTransactions;

class EdHelper
{
	const F_STATUS = 'status';
	const F_ERRORS = 'errors';
	const F_HIDDEN = 'hidden';
	const F_MODE   = 'mode';
	const F_FIELDS = 'fields';
	const F_TRANSACTION = 'transaction';
	const F_RECEIPT  = 'receipt';
	const F_SUM = 'sum';
	const F_COMMISSION = 'clientCommissionAmount';
	const F_SERVICE_CODE = 'serviceCode';
	const F_PAYMENT_ID = 'paymentId';
	const F_REDIRECT = 'redirect';
	const F_RECEIVER = 'receiver';
	const F_SENDER = 'sender';
	const F_SERVER_NAME = 'serverName';
	const F_SERVER_TIME = 'serverTime';
	const F_PAN = 'pan';
	const F_SID = 'sid';
	const F_MTS_MONEY_SESSION = 'mts_session';
	const F_DATE = 'date';
	const F_SUMMARY = 'summary';

	const MODE_START = 'start';
	const MODE_FIELDS = 'fields';
	const MODE_PAY = 'pay';
	const MODE_AUTH = 'auth';
	const MODE_CONFIRM = 'confirm';

	const ERROR_MSG_REMOVED_SERVICE = 'Данная услуга удалена или временно недоступна.';
	const ERROR_MSG_FORBIDDEN_FOR_GUEST = 'Доступ к данной услуге разрешен только для авторизованных пользователей.';

	public static $phoneField = [
		'hint' => 'С баланса этого номера будет произведена оплата счета',
		'id' => 'phoneField',
		'description' => 'Номер телефона для оплаты',
		'name' => 'phone',
		'type' => 'N',
		'placeholder' => '( 375 ) ** - *** - ****',
		'editable' => true,
		'originalField' => false,
		'required' => true,
	];

	public static $codeField = [
		'hint' => 'Для подтверждения платежа введите код из SMS-сообщения',
		'id' => 'codeField',
		'description' => 'Код подтверждения',
		'name' => 'password',
		'type' => 'I',
		'editable' => true,
		'maxLength' => 4,
		'minLength' => 4,
		'originalField' => false,
		'required' => true,
	];

	public static function getEripApiUrl()
	{
		return ERIP_API_URL . '/api_v2.php';
	}

	public static function getSumField(array $sum, $maxSum = null)
	{
		$field = [
			'description' => 'Сумма' . (isset($sum['currency']) ? ' (' . $sum['currency'] . ')' : ''),
			'value' => !isset($sum['value']) || empty($sum['value']) ? '' : (float) $sum['value'],
			'min' => isset($sum['min']) ? $sum['min'] : null,
			'name' => 'sum',
			'type' => 'R',
			'isSum' => true,
			'editable' => true,
			'originalField' => false,
			'required' => true
		];

		/**
		 * @todo лимиты по сумме от ерипа и от нас - это разные вещи, поскольку для ерипа ограничения распространяются
		 * на сумму без комиссии, а у нас на сумму с комиссией, поэтому валидацию на клиенте в дальнейшем нужно будет
		 * разделить.
		 */
		$field['max'] = (isset($sum['max'], $maxSum)) ? min($sum['max'], $maxSum) : max((int) isset($sum['max']) ? $sum['max'] : 0, (int) $maxSum) ;

		if (isset($sum['editable'])) {
			$field['readonly'] = !$sum['editable'];
		}
		if (isset($sum['nominal'])) {
			$field['nominal'] = $sum['nominal'];
		}

		return $field;
	}

	public static function eripPayment(PaymentTransactions $paymentTransactions, $logMessage)
	{
		$data = [
			'serviceCode' => $paymentTransactions->service_id,
			'paymentId' => $paymentTransactions->getPaymentId(),
			'mode' => 'confirm',
			'receipt' => $paymentTransactions->id
		];

		$caller = new WsCaller(self::getEripApiUrl());
		$caller->bulkSetGetParameters($data);
		$result = $caller->callDecodeJson();
		yii::info(new $logMessage($paymentTransactions->getPaymentId(), $data, $result, $paymentTransactions->user_id), 'erip_payment');

		return $result;
	}

	public static function eripCancel(PaymentTransactions $transaction, $logMessage)
	{
		$paymentId = $transaction->getPaymentId();

		Yii::info('Trying cancel payment for ' . $transaction->uuid . ' with paymentId = ' . $paymentId);

		if (empty($paymentId)) {
			return true;
		}

		return self::eripCancelByPaymentId($paymentId, $logMessage, $transaction->user_id);
	}

	public static function eripCancelByPaymentId($paymentId, $logMessage, $userId = null)
	{
		$data = [
			'mode' => 'cancel',
			'paymentId' => $paymentId,
			'cancelReason' => 'Время ожидания истекло'
		];

        $caller = new WsCaller(self::getEripApiUrl());
        $caller->bulkSetGetParameters($data);
        $result = $caller->callDecodeJson();
		yii::info(new $logMessage($paymentId, $data, $result, $userId), 'erip_payment');

		if (isset($result['success']) && $result['success'] === true) {
			return true;
		}
		MailService::sendAdminReportOnTransactionCancelFail($paymentId, $data, $result);

		return false;
	}
}
