<?php
namespace frontend\modules\mobile\models\virtual;

use Yii;
use yii\helpers\ArrayHelper;

class EndRegistration extends \frontend\models\virtual\EndRegistration
{
	public function attributeHints()
	{
		$hints = parent::attributeHints();
		$hints['fio'] = ArrayHelper::remove($hints, 'password');
		return $hints;
	}
}
