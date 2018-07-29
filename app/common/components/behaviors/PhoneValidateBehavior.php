<?php
namespace common\components\behaviors;

use common\components\services\PhoneService;
use yii\base\Behavior;
use yii\base\Model;

class PhoneValidateBehavior extends Behavior
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
		if (empty($this->owner->phone)) {
			$this->owner->addError('phone', 'Необходимо заполнить «' . $this->owner->getAttributeLabel('phone') . '»');
			return false;
		} elseif (PhoneService::isBelorussPhone($this->owner->phone) === false) {
			$this->owner->addError('phone', 'Сервис только для абонентов белорусского МТС');
			return false;
		}
	}

	public function afterValidate()
	{
		if (strlen($this->owner->phone) != 12) {
			$this->owner->addError('phone', 'Неверно заполнен «' . $this->owner->getAttributeLabel('phone') . '»');
		}
	}
}