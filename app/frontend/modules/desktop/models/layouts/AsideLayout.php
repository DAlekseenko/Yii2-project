<?php

namespace frontend\modules\desktop\models\layouts;

use yii;
use common\models\layouts\AbstractLayout;

class AsideLayout extends AbstractLayout
{
	protected $_template = '/layouts/_asideGuest.php';
	protected $_userTemplate = '/layouts/_aside.php';

	public function prepare()
	{
		if (!Yii::$app->user->getIsGuest()) {
			$this->setTemplate($this->_userTemplate);
		}
		return $this;
	}
}
