<?php

namespace common\models;

/**
 * @property string   $id
 * @property int 	  $expire
 * @property string   $data
 * @property int|null $user_id
 */
class Session extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'session';
	}
}
