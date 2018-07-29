<?php

namespace common\models;

/**
 * @property string  $order_number
 * @property integer $user_id
 * @property float	 $sum
 * @property string	 $status
 * @property string  $type
 * @property array	 $data
 * @property array	 $assist_data
 * @property string	 $date_create
 * @property string	 $date_pay
 * @property array	 $params
 * @property Users   $user
 */

class AssistTransactions extends AbstractModel
{
	use traits\RelationUser;

	const TYPE_RECHARGE = 'Recharge';
	const TYPE_INVOICE  = 'Invoice';
	const TYPE_PAYMENT  = 'Payment';

	const STATUS_NEW = 'New';
	const STATUS_IN_PROCESS = 'In Process';
	const STATUS_DELAYED = 'Delayed';
	const STATUS_APPROVED = 'Approved';
	const STATUS_PARTIAL_APPROVED = 'PartialApproved';
	const STATUS_PARTIAL_DELAYED = 'PartialDelayed';
	const STATUS_CANCELED = 'Canceled';
	const STATUS_PARTIAL_CANCELED = 'PartialCanceled';
	const STATUS_DECLINED = 'Declined';
	const STATUS_TIMEOUT = 'Timeout';

	public static function tableName()
	{
		return 'assist_transactions';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['order_number', 'status', 'type'], 'string'],
			[['user_id'], 'integer'],
			[['date_create', 'date_pay'], 'safe'],
			[['sum'], 'number'],
		];
	}

	/**
	 * Возвращает подробную информацию о платеже, которую можно отдать пользователю.
	 *
	 * @return array|null
	 */
	public function getPaymentInfo()
	{
		$tz = new \DateTimeZone('Europe/Minsk');
		$date = date('d.m.Y H:i:s', strtotime($this->assist_data['orderdate']) + $tz->getOffset(new \DateTime()));

		return empty($this->assist_data) ? null : [
			'order_number' => $this->order_number,
			'sum'          => $this->sum,
			'currency'     => GLOBAL_CURRENCY,
			'billnumber'   => $this->assist_data['billnumber'],
			'orderdate'    => $date,
			'approvalcode' => isset($this->assist_data['approvalcode']) ? $this->assist_data['approvalcode'] : 'N/A',
			'cardholder'   => $this->assist_data['cardholder'],
			'meannumber'   => $this->assist_data['meannumber'],
			'meantypename' => $this->assist_data['meantypename'],
			'firstname'    => $this->assist_data['firstname'],
			'lastname'     => $this->assist_data['lastname'],
			'orderstate'   => $this->assist_data['orderstate'],
			'message'      => isset($this->assist_data['message']) && !empty($this->assist_data['message']) ? $this->assist_data['message'] : 'N/A'
		];
	}

	public function getTransactions()
	{
		return empty($this->data) ? [] : PaymentTransactions::find()->where(['user_id' => $this->user_id, 'id' => $this->data])->all();
	}
}
