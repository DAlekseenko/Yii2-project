<?php
namespace common\components\widgets;

use yii;

class ServiceIcon extends Icon
{
	public function init()
	{
		if ($this->tagName === 'a' && !isset($this->options['href'])) {
			$this->options['href'] = yii\helpers\Url::to(['/payments/pay', 'id' => $this->item['id']]);
		}

		$this->prependStringValue($this->options, '-service');
		parent::init();
	}
}