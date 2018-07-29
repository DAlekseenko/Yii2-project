<?php
namespace api\components\rbac;

use yii\rbac\DbManager;

class AuthManager extends DbManager {

	public function checkAccess($userId, $permissionName, $params = [])
	{
		if ($permissionName == 'api-user' && \Yii::$app->user->isGuest) {
			//TODO здесь проверка какая-то
			return true;
		}
		return parent::checkAccess($userId, $permissionName, $params);
	}
}