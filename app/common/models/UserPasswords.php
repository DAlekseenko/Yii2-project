<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_passwords".
 *
 * @property integer $user_id
 * @property string $password
 * @property string $date_create
 *
 * @property Users $user
 */
class UserPasswords extends \yii\db\ActiveRecord
{
	const PASSWORD_LIVE_TIME = 900;

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'user_passwords';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['user_id', 'password'], 'required'],
			[['user_id'], 'integer'],
			[['date_create'], 'safe'],
			[['password'], 'string', 'max' => 32],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'user_id' => 'User ID',
			'password' => 'Password',
			'date_create' => 'Date Create',
		];
	}

	public function validatePassword($password)
	{
		/** @var \common\components\services\Environment $environment */
		$environment = Yii::$app->environment;

		yii::info("User code validation: in DB: {$this->password}, user input: $password, user_id: {$this->user_id}, env: {$environment->getName()}", 'notifications');
		return $this->password === $password;
	}

	public function generatePassword()
	{
		$this->password = rand(1000, 9999);
	}

	/**
	 * @return int
	 */
	public static function clearOldPasswords()
	{
		return self::deleteAll('date_create < \'' . date('Y-m-d H:i:s', time() - self::PASSWORD_LIVE_TIME) . '\'');
	}
}