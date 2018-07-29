<?php

namespace frontend\modules\mobile\models\layouts;

use yii;
use common\models\layouts\AbstractLayout;

class UserMenu extends AbstractLayout
{
	protected $_template = '//partial/user/menu.php';

	public function prepare()
	{
		if (Yii::$app->user->getIsGuest()) {
			$this->disable();
		}
		return $this;
	}
}
