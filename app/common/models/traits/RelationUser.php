<?php

namespace common\models\traits;

use common\models\Users;

trait RelationUser
{
	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getUser()
	{
		return $this->hasOne(Users::className(), ['user_id' => 'user_id']);
	}
}
