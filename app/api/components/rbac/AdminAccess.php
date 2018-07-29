<?php

namespace api\components\rbac;

use api\models\admin\virtual\AdminMenu;
use Yii;
use api\models\admin\ActionsRole;
use yii\filters\AccessControl;
use api\models\admin\Users;
use yii\web\HttpException;

/**
 * AdminAccess  class Access admin actions
 * @property Users $user
 * @property array $roles
 */
class AdminAccess extends AccessControl
{
    public $user;
    public $roles;
    const YII_ACTION_PREFIX = 'action';

    public function init()
    {
        $userId = Yii::$app->user->id;
        if(!$userId){
            throw new HttpException(403);
        }
        $this->user = Users::findOne($userId);
        $this->roles = Yii::$app->authManager->getRolesByUser($userId);
    }


    public function beforeAction($action)
    {
        if ($this->user && $this->user->hasRole(Users::ROLE_ADMIN)) {
            return true;
        }
        $actionsRole = $this->getActions($action->controller->id);
        $action = str_replace(self::YII_ACTION_PREFIX,'',$action->actionMethod);
        if ($actionsRole && in_array(AdminMenu::getPrepareActionName($action), $actionsRole->actions)) {
            return true;
        }
        throw new HttpException(406);
    }

    public function getActions($controller)
    {
        return ActionsRole::find()
            ->select(['actions'])
            ->where(['IN', 'role_name', array_keys($this->roles)])
            ->andWhere(['controller' => $controller])
            ->one();
    }

}