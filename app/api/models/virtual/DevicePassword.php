<?php

namespace api\models\virtual;

use Yii;
use yii\base\Model;
use common\models\UserDevices;

class DevicePassword extends Model
{
	public $device_id;
	public $password;
	public $access_token;

	public function rules()
	{
		return [
			[['device_id', 'password', 'access_token'], 'required'],
		];
	}

	public function check()
	{
		if (!$this->validate()) {
			return false;
		}

		/**@var \common\models\UserDevices $userDevice */
		$userDevice = $this->getUserDevice();
		if (!$userDevice || !$userDevice->validatePassword($this->password)) {
			$this->addError('password', 'Введен неправильный код');
			return false;
		}

		return true;
	}

	public function resetPassword()
	{
		if (!$this->validate()) {
			return false;
		}

		$model = $this->getUserDevice();
		$model->setPassword($this->password);
		if ($model->save()) {
			return true;
		}

		$this->addErrors($model->getErrors());
		return false;
	}

	private function getUserDevice()
	{
		return UserDevices::find()->where(['device_id' => $this->device_id, 'access_token' => $this->access_token, 'user_id' => \Yii::$app->user->identity->user_id])->one();
	}
}
