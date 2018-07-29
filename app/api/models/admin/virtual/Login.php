<?php
namespace api\models\admin\virtual;

use api\models\admin\Users;
use common\components\behaviors\AdminPhoneValidateBehavior;

class Login extends \common\models\virtual\Login
{
	public function behaviors()
	{
		return [
			'phoneValidateBehavior' => [
				'class' => AdminPhoneValidateBehavior::className()
			]
		];
	}

	/**
	 * @return Users|null
	 */
	public function getUser()
	{
		if ($this->_user === null) {
			$this->_user = Users::findByPhone($this->phone);
		}
		return $this->_user;
	}
}
