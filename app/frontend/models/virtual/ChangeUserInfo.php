<?php
namespace frontend\models\virtual;

use Yii;
use yii\base\Model;

class ChangeUserInfo extends Model
{
	public $first_name;
	public $last_name;
	public $patronymic;

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['first_name', 'last_name', 'patronymic'], 'string'],
		];
	}

	public function attributeLabels()
	{
		return [
			'first_name' => 'Имя',
			'last_name' => 'Фамилия',
			'patronymic' => 'Отчество'
		];
	}

	public function changeUserInfo()
	{
		if ($this->validate()) {
			$user = Yii::$app->user->identity;
			$user->setAttributes($this->getAttributes());
			return $user->update(false) !== false;
		}
		return false;
	}
}
