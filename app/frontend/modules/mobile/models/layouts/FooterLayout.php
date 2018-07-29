<?php

namespace frontend\modules\mobile\models\layouts;

use common\components\lib\Device;
use common\models\layouts\AbstractLayout;

class FooterLayout extends AbstractLayout
{
	protected $_template = '/layouts/_footer.php';

	public function init()
	{
		$this->setVar('iosLink', \yii::$app->clientDevice->iosStoreLink());
		$this->setVar('androidLink', \yii::$app->clientDevice->androidStoreLink());
	}
}