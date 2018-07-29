<?php

namespace frontend\models;

use Yii;

class Users extends \common\models\Users
{

	public function afterSave($insert, $changedAttributes)
	{
		if ($insert) {
			Yii::$app->authManager->assign(Yii::$app->authManager->getRole('user'), $this->getPrimaryKey());
		}
		parent::afterSave($insert, $changedAttributes);
	}
}