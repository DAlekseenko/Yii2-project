<?php
namespace common\models\virtual;

use common\components\behaviors\PhoneValidateBehavior;
use common\models\Locations;
use Yii;
use frontend\models\Users;
use yii\base\Model;

/** @deprecated  */
class Login extends Model
{
	const BLOCK_TIME = 300;

	public $phone;
	public $password;

	protected $_user;

	public function behaviors()
	{
		return [
			'phoneValidateBehavior' => [
				'class' => PhoneValidateBehavior::className()
			]
		];
	}

	public function rules()
	{
		return [
			[['phone', 'password'], 'required'],
			['phone', 'validatePhone'],
			['password', 'validatePassword'],
		];
	}

	public function attributeLabels()
	{
		return [
			'phone' => 'Номер телефона',
			'password' => 'Пароль',
		];
	}

	/**
	 * Validates the password.
	 * This method serves as the inline validation for password.
	 *
	 * @param string $attribute the attribute currently being validated
	 * @param array $params the additional name-value pairs given in the rule
	 */
	public function validatePassword($attribute, $params)
	{
		$tryCounter = (int) Yii::$app->cache->get('valid_' . $this->phone);

		if (!$this->hasErrors() && (++$tryCounter > 3 || !$this->checkPassword())) {
			if ($tryCounter > 3) {
				$this->addError($attribute, 'Ваш логин заблокирован на 5 минут. Повторите попытку позже');
			} else {
				Yii::$app->cache->set('valid_' . $this->phone, $tryCounter, self::BLOCK_TIME);
			}
			$this->addError($attribute, 'Неправильный ' . $this->getAttributeLabel('password'));
		} else {
			Yii::$app->cache->delete('valid_' . $this->phone);
		}
	}

	public function validatePhone($attribute, $params)
	{
		$user = $this->getUser();
		if (empty($user)) {
			$this->addError($attribute, 'Абонент не найден');
			return false;
		}

		if ($user->isBanned()) {
			$this->addError($attribute, 'Номер заблокирован');
			return false;
		}
	}

	public function checkPassword()
	{
		$user = $this->getUser();
		return $user && ($user->checkPassword($this->password) || $user->userPassword && $user->userPassword->validatePassword($this->password));
	}

	/**
	 * Logs in a user using the provided phone and password.
	 * @return boolean whether the user is logged in successfully
	 */
	public function login()
	{
		if ($this->validate()) {
			return Yii::$app->user->login(Users::getRealUser($this->phone), 3600 * 24 * 30);
		} else {
			return false;
		}
	}

	/**
	 * @deprecated
	 *
	 * Finds user by [[phone]]
	 * @return Users|null
	 */
	public function getUser()
	{
		if ($this->_user === null) {
			$this->_user = Users::findByPhone($this->phone);
			if (!empty($this->_user) && isset($_COOKIE['location_id']) && $this->_user->location_id == null) {
				$location = Locations::findById($_COOKIE['location_id']);
				if (!empty($location)) {
					$this->_user->location_id = $location->id;
					$this->_user->save();
				}
			}
		}

		return $this->_user;
	}
}
