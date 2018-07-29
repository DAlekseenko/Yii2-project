<?php

namespace console\models;

use Yii;

class Categories extends \common\models\Categories
{
	public function setAttributesByItem($item)
	{
		$item['id']  = $item['eripCode'];
		$item['key'] = $item['uniqueCategoryKey'];
		$this->setAttributes($item, false);
	}
}