<?php

namespace frontend\models;

use Yii;

//эта моделька используется при добавлении\удалении записей в таблице на странице invoices(внизу)
class InvoicesUsersData extends \common\models\InvoicesUsersData
{
	public function beforeSave($insert)
	{
		$this->user_id = Yii::$app->user->id;
		$this->visible_type = InvoicesUsersData::VISIBILITY_USER;
		return parent::beforeSave($insert);
	}
}