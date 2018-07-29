<?php
namespace api\components\web;

class User extends \yii\web\User
{
	protected function renewAuthStatus()
	{
		parent::renewAuthStatus();
		if (!$this->getIdentity(false) && $token = \Yii::$app->request->get('access_token')) {
			/* @var $class \yii\web\IdentityInterface */
			$class = $this->identityClass;
			$identity = $class::findIdentityByAccessToken($token);
			if ($identity) {
				$this->setIdentity($identity);
			}
		}
	}
}