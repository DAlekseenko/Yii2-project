<?php

namespace frontend\modules\mobile\models\layouts;

use yii;
use common\models\layouts\AbstractLayout;

class TopPanel extends AbstractLayout
{
	protected $_template = '/layouts/_topPanelGuest.php';
	protected $_userTemplate = '/layouts/_topPanel.php';

	public function prepare()
	{
		if (!Yii::$app->user->getIsGuest()) {
			$this->setTemplate($this->_userTemplate);
		}
		return $this;
	}
}
