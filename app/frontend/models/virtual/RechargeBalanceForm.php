<?php

namespace frontend\models\virtual;

use common\models\AssistTransactions;
use common\models\PaymentTransactions;
use common\models\Users;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class RechargeBalanceForm extends Model
{
	const MAX_PAY = '499.99';

	protected $completed = false;

	protected $action = '';

	protected $phone;

	public $user_id;

	/** @var  AssistTransactions|null */
	public $order;

	public $first_name;

	public $last_name;

	public $sum;

	public $email;

	public $comment;

	private $rechargeTeaser = null;

	public function prepare()
	{
		$this->completed = true;
		$this->action = ASSIST_USER_REDIRECT_URL;
	}

	public function getAction()
	{
		return $this->action;
	}

	public function getPhone()
	{
		return $this->phone;
	}

	public function isComplete()
	{
		return $this->completed == true && !$this->hasErrors();
	}

	public function getRub()
	{
		$sum = (float)$this->sum;

		return ($sum > 0) ? (int)$sum : '';
	}

	public function getKop()
	{
		$sum = (float)$this->sum;
		$parts = explode('.', (string)$sum);

		return ($sum > 0 && isset($parts[1])) ? $parts[1] : '';
	}

	public function getKey()
	{
		$signature = '';
		$signatureSource = ASSIST_MERCHANT_ID . ';' . $this->order->order_number . ';' . $this->sum . ';' . GLOBAL_CURRENCY . ';' . $this->getPhone();
		$privateKey = file_get_contents(ROOT_DIR . '/assist_private_key.pem');
		openssl_get_privatekey($privateKey);
		openssl_sign($signatureSource, $signature, $privateKey, OPENSSL_ALGO_MD5);

		return base64_encode($signature);
	}

	public function rules()
	{
		return [
			[['first_name', 'last_name', 'email', 'sum'], 'required'],
			[['first_name', 'last_name', 'email'], 'string', 'max' => 32],
			[['comment'], 'string', 'max' => 100],
			['email', 'email'],
			['sum', 'double', 'min' => '0.01', 'max' => self::MAX_PAY]
		];
	}

	public function attributeLabels()
	{
		return [
			'first_name' => 'Имя',
			'last_name'  => 'Фамилия',
			'comment'    => 'Комментарий',
			'email'      => 'E-mail',
			'sum'        => 'Сумма платежа (' . GLOBAL_CURRENCY . ')'
		];
	}

	public function init()
	{
		$user = Yii::$app->user->identity;

		$this->phone = $user->phone;
		$this->first_name = $user->first_name;
		$this->last_name = $user->last_name;
		$this->email = $user->email;
		$this->user_id = $user->user_id;
	}

	public function save($type = AssistTransactions::TYPE_RECHARGE, array $assistTransactionData = null, $env = null)
	{
		$user = Yii::$app->user->identity;
		$user->first_name = empty($user->first_name) ? $this->first_name : $user->first_name;
		$user->last_name = empty($user->last_name) ? $this->last_name : $user->last_name;
		$user->email = $this->email;

		$assistTransaction = new AssistTransactions();
		$assistTransaction->order_number = 'acc-' . $user->phone . '_' . strtoupper(substr(md5(time() . strtoupper(rand(10000, 99999))), 0, 7));
		$assistTransaction->user_id = $user->user_id;
		$assistTransaction->sum = $this->sum;
		$assistTransaction->type = $type;
		$assistTransaction->status = $assistTransaction::STATUS_NEW;
		if (isset($env) && !isset($assistTransaction->params['onCreateEnv'])) {
			$assistTransaction->params = ['onCreateEnv' => $env];
		}

		if (isset($assistTransactionData)) {
			$assistTransaction->data = $assistTransactionData;
		}
		$this->order = $assistTransaction;

		return $user->save() && $assistTransaction->save();
	}

	public function getAssistRequestPostFields()
	{
		if (empty($this->order)) {
			return [];
		}
		$result = [
			'Merchant_ID'    => ASSIST_MERCHANT_ID,
			'Signature'      => $this->getKey(),
			'OrderCurrency'  => GLOBAL_CURRENCY,
			'CustomerNumber' => $this->getPhone(),
			'URL_RETURN_OK'  => EXTERNAL_URL . 'user/recharge-ok',
			'URL_RETURN_NO'  => EXTERNAL_URL . 'user/recharge-no',
			'OrderNumber'    => $this->order->order_number,
			'MobilePhone'    => $this->getPhone(),
			'OrderAmount'    => $this->sum,
			'Firstname'      => $this->first_name,
			'Lastname'       => $this->last_name,
			'Email'          => $this->email,
		];
		if (YII_ENV == 'prod') {
			$result['account'] = $this->getPhone();
			$result['CardPayment'] = '1';
			$result['MobiconPayment'] = '0';
		}

		return ['data' => $result, 'assist_url' => ASSIST_USER_REDIRECT_URL];
	}

	/**
	 * @param \common\models\Users $user
	 * @param array|PaymentTransactions[] $transactions
	 *
	 * @return bool
	 */
	public function canPayTransactions(Users $user, array $transactions = [])
	{
		$totalSum = array_sum(ArrayHelper::getColumn($transactions, 'sum'));

		if ($user->canPay($totalSum, $text)) {
			return true;
		}
		// у пользователя недостаточно средств:
		$balance 	= $user->getBalance();  //Текущий баланс
		$need		= $totalSum + Users::BALANCE_ALLOWED_REMAIN;	 //Сколько необходимо иметь на балансе средств для оплаты
		$difference = $need - $balance;

		if ($difference <= RechargeBalanceForm::MAX_PAY && $totalSum <= RechargeBalanceForm::MAX_PAY) {
			$this->setRechargeTeaser($totalSum, $difference, $balance, Users::BALANCE_ALLOWED_REMAIN);
			return true;
		}
		$this->addError('sum', $text);
		return false;
	}

	/**
	 * @param float	$sum 		- сумма платежа.
	 * @param float	$need		- необходимая сумма на балансе.
	 * @param float $balance	- текущий баланс пользователя.
	 * @param float $res		- обязательный остаток.
	 */
	private function setRechargeTeaser($sum, $need, $balance, $res)
	{
		$text = [
			'На балансе мобильного телефона недостаточно средств.',
			'С учетом обязательного остатка в {{res}} {{currency}} для проведения платежа на {{sum}} {{currency}} не хватает {{need}} {{currency}}.',
		];
		$suggested_sum = [$need];

		if ($balance > $res && $sum <= RechargeBalanceForm::MAX_PAY) {
			$text[] = 'Проведите платеж, пополнив баланс мобильного телефона с банковской карты на:';
			$suggested_sum[] = $sum;
		} else {
			$text[] = 'Проведите платеж, пополнив баланс мобильного телефона с банковской карты на {{need}} {{currency}}';
		}

		$this->rechargeTeaser = [
			'text' => $text,
			'suggested_sum' => $suggested_sum,
			'params' => [
				'sum' => number_format($sum, 2),
				'need' => number_format($need, 2),
				'balance' => number_format($balance, 2),
				'res' => number_format($res, 2),
				'currency' => GLOBAL_CURRENCY
			]
		];
	}

	public function getRechargeTeaser()
	{
		return $this->rechargeTeaser;
	}
}
