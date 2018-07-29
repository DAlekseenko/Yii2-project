<?php

namespace frontend\models\layouts;

use yii;
use common\models\virtual\Login;
use common\models\layouts\AbstractLayout;

class LoginLayout extends AbstractLayout
{
	public function prepare()
	{
		$loginFormModel = new Login();
		$loginFormModel->load(Yii::$app->request->post());
		$this->makeProperties([
			'loginFormModel' => $loginFormModel,
		]);
		return $this;
	}
}

