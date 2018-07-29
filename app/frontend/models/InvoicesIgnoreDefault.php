<?php

namespace frontend\models;

use common\models\Services;
use Yii;

/**
 * This is the model class for table "invoices_ignore_default".
 *
 * @property integer $category_id
 * @property integer $user_id
 * @property string $key
 *
 * @property Users $user
 */
class InvoicesIgnoreDefault extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'invoices_ignore_default';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['key'], 'required'],
			[['user_id'], 'integer']
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'key' => 'Ключ категории',
			'user_id' => 'User ID',
		];
	}

	public function beforeSave($insert)
	{
		if ($insert) {
			$this->user_id = Yii::$app->user->id;
		}
		return parent::beforeSave($insert);
	}

	public static function primaryKey()
	{
		return ['user_id', 'key'];
	}

	public static function findIgnored()
	{
		return self::find()->select('key')->where(['user_id' => Yii::$app->user->id])->asArray()->column();
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getUser()
	{
		return $this->hasOne(Users::className(), ['user_id' => 'user_id']);
	}
}