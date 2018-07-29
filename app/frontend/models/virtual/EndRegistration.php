<?php
namespace frontend\models\virtual;

use Yii;
use yii\base\Model;

class EndRegistration extends Model
{
	public $fio, $password, $passwordRepeat;

	public function rules()
	{
		return [
			[['password', 'passwordRepeat'], 'required'],
			[['password', 'passwordRepeat'], 'string', 'min' => 4],
			['passwordRepeat', 'compare', 'compareAttribute' => 'password'],
			[['fio'], 'string'],
		];
	}

	public function attributeHints()
	{
		return [
			'password' => 'Текущий временный код будет действителен только в течение 15 мин.'
		];
	}

	public function attributeLabels()
	{
		return [
			'fio' => 'Фамилия Имя Отчество',
			'password' => 'Постоянный пароль',
			'passwordRepeat' => 'Повторите пароль',
		];
	}

	public function changeUserInfo()
	{
		if ($this->validate()) {
			$user = Yii::$app->user->identity;
			$user->setAttributes($this->getUserAttributes());
			$user->setPassword($this->password);
			return $user->update(false) !== false;
		}
		return false;
	}

	public function getUserAttributes()
	{
		$result = [];
		$fullName = trim($this->fio);
		if ($fullName) {
			$fullName = explode(' ', $fullName);
			$result['last_name'] = $fullName[0];
			$result['first_name'] = isset($fullName[1]) ? $fullName[1] : '';
			$result['patronymic'] = isset($fullName[2]) ? implode(' ', array_slice($fullName, 2)) : '';
		}
		return $result;
	}
}
