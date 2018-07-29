<?php

namespace common\components\behaviors;

use yii\base\Behavior;
use yii\base\Model;

class AdminPhoneValidateBehavior extends Behavior
{
	public function events()
	{
		return [
			Model::EVENT_BEFORE_VALIDATE => 'beforeValidate',
			Model::EVENT_AFTER_VALIDATE => 'afterValidate',
		];
	}

	public function beforeValidate()
	{
		$this->owner->phone = preg_replace('/[^0-9]/', '', is_array($this->owner->phone) ? implode('', $this->owner->phone) : $this->owner->phone);
	}

	public function afterValidate()
	{
		if (strlen($this->owner->phone) != 12) {
			$this->owner->addError('phone', 'Неверно заполнен «' . $this->owner->getAttributeLabel('phone') . '»');
		}
	}
}