<?php
namespace common\components\widgets;

use yii;

class CategoryIcon extends Icon
{
	public function init()
	{
		if ($this->tagName === 'a' && !isset($this->options['href'])) {
			$this->options['href'] = yii\helpers\Url::to(['/categories', 'id' => $this->item['id']]);
		}

		$this->prependStringValue($this->options, '-category');
		parent::init();
	}
}