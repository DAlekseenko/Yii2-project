<?php

namespace common\models\virtual;

use Yii;
use yii\base\Model;
use common\models\Users;
use common\components\services\Environment;
use PbrLibBelCommon\Protocol\Subscription\Service\DTO\SubscriptionState;

class Registration extends Model
{
	const PHONE_LEN = 12;

	public $phone;

	public function rules()
	{
		return [['phone', 'required']];
	}

	public function attributeLabels()
	{
		return [
			'phone' => 'Номер телефона',
		];
	}

	public function afterValidate()
	{
		if (strlen($this->phone) != static::PHONE_LEN) {
			$this->addError('phone', 'Неверно заполнен «' . $this->getAttributeLabel('phone') . '»');
		}
	}

	public function load($data, $formName = null)
	{
		if (parent::load($data, $formName) === false) {
			return false;
		}

		if (is_array($this->phone)) {
			@list($code, $phone) = array_values($this->phone);
			if (empty($phone)) {
				$this->phone = '';
			} else {
				$this->phone = $code . $phone;
			}
		}
		$this->phone = preg_replace('/[^0-9]/', '', $this->phone);

		return true;
	}

	/**
	 * @param  Environment|null $env
	 * @return Users
	 * @throws \Exception
	 */
	public function getUser(Environment $env = null)
	{
		$transaction = Yii::$app->getDb()->beginTransaction();

		try {
			$user = Users::find()->byPhone($this->phone)->oneForUpdate() ?: new Users();
			if ($user->isNewRecord) {
				$user->phone = $this->phone;
				$user->subscription_status = Users::USER_TYPE_REQUEST;
			}
			$user->setEnv($env);

			if ($user->save() === false) {
				$errors = implode('; ', $user->getFirstErrors());
				throw new \Exception("Cant save user:\n$errors");
			}
			$transaction->commit();

			return $user;

		} catch (\Exception $e) {
			$transaction->rollBack();
			throw $e;
		}
	}

	/**
	 * @param Users $user
	 * @return bool
	 * @throws \Exception
	 * @throws \Throwable
	 */
	public function deleteTempUser(Users $user)
	{
		if ($user->isRequest()) {
			return (bool) $user->delete();
		}
		return true;
	}
}
