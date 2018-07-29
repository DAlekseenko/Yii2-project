<?php
namespace frontend\models\virtual;

use common\models\virtual\Login;

class SendPasswordSuccessForm extends Login
{
	public function attributeLabels()
	{
		return [
			'phone' => 'Номер телефона',
			'password' => 'Код из SMS',
		];
	}
}
