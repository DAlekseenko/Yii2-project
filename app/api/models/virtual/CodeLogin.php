<?php

namespace api\models\virtual;

use common\models\virtual\Login;

class CodeLogin extends Login
{
	public function attributeLabels()
	{
		return [
			'phone' => 'Номер телефона',
			'password' => 'Код',
		];
	}
}