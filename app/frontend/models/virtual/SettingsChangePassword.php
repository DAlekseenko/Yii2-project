<?php
namespace frontend\models\virtual;

use Yii;
use yii\base\Model;


class SettingsChangePassword extends Model
{
	public $oldPassword;
	public $password;
	public $passwordRepeat;

	public function rules()
	{
		return [
			[['password', 'passwordRepeat'], 'required'],
			['password', 'string', 'min' => 8],
			['oldPassword', 'string'],
			['passwordRepeat', 'compare', 'compareAttribute' => 'password'],
		];
	}

	public function attributeLabels()
	{
		return [
			'password' => 'Новый пароль',
			'oldPassword' => 'Старый пароль',
			'passwordRepeat' => 'Повторите пароль',
		];
	}

	public function attributeHints()
	{
		return [
			'password' => 'Минимальная длина 8 символов. Должен содержать цифры, заглавные и строчные символы'
		];
	}

	public function validate($attributeNames = null, $clearErrors = true)
	{
		if (!preg_match("/[0-9]/", $this->password) || !preg_match("/[a-zа-я]/u", $this->password) || !preg_match("/[A-ZА-Я]/u", $this->password)) {
			$this->addError('password', 'Пароль должен содержать цифры, заглавные и строчные символы');
			return false;
		}
		return parent::validate();
	}

	public function changePassword()
	{
		if ($this->validate()) {
			$user = Yii::$app->user->identity;
			$user->setPassword($this->password);
			return $user->update(false);
		}
		return false;
	}
}
