<?php

namespace api\models\admin\virtual;

use api\models\admin\Users;
use \yii\db\ActiveRecord;
use api\models\admin\ActionsRole;
use yii\helpers\ArrayHelper;

/**
 * @property string $sub_domain
 * @property string $name
 * @property string $role_name
 * @property integer $id
 * @property integer $parent_id
 */
class AdminMenu extends ActiveRecord implements \JsonSerializable
{
    const ADMIN_MAIN = 'admin';
    const NO_MAIN = NULL;
    const CONTROLLERS_PATH = 'api\controllersAdmin\\';

    public $parent_name;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'main_menu';
    }

    /**
     * Get Menu By User Role
     * @param Users $user
     * @return array
     */
    public static function getByUser(Users $user)
    {
        if ($user->hasRole($user::ROLE_ADMIN)) {
            $main = self::getAdminMain();
        } else {
            $main = !empty($user->roles) ? self::getMainByRole($user->roles) : [];
        }
        return $main;
    }

    /**
     * Only Admin main
     * @return array|ActiveRecord[]
     */
    public static function getAdminMain()
    {
        $main = self::find()->where(['parent_id' => null])->orderBy('id')->asArray()->all();
        foreach ($main as $key => $mainItem) {
            if ($mainItem['sub_domain'] == 'false') {
                $main[$key]['sub_main'] = self::find()->where(['parent_id' => $mainItem['id']])->orderBy('id')->asArray()->all();
            }
        }
        return $main;
    }

    /**
     * Get Menu By role
     * @param $roles
     * @return array
     */
    public static function getMainByRole($roles)
    {
        $mm = self::tableName();
        $ar = ActionsRole::tableName();
        $main = [];
        $constrains = ArrayHelper::getColumn($roles, 'name');

        $subQuery = self::find()
            ->select(['id', 'role_name'])
            ->leftJoin($ar, "$mm.sub_domain = $ar.controller");

        $dataMain = self::find()
            ->select(["$mm.id", "$mm.sub_domain", "$mm.name", "$mm.parent_id", "t2.name AS parent_name"])
            ->leftJoin(['accepted_main' => $subQuery], "accepted_main.id = $mm.id")
            ->leftJoin('main_menu t2', "t2.id = $mm.parent_id")
            ->where(['IN', "accepted_main.role_name", $constrains])
            ->orderBy("$mm.id");

        foreach ($dataMain->each() ?: [] as $mainItem) {
            if ($mainItem->parent_id) {
                $main[$mainItem->parent_id]['sub_domain'] = 'false';
                $main[$mainItem->parent_id]['name'] = $mainItem->parent_name;
                $main[$mainItem->parent_id]['sub_main'][] = $mainItem;
            } else {
                $main[$mainItem->id] = $mainItem;
            }
        }
        return $main;
    }

    /**
     * get All admin controllers and their actions
     * @param $name
     * @return array
     */
    public static function getActionsForController($name)
    {
        $controller = new \ReflectionClass(self::CONTROLLERS_PATH . self::getControllerName($name));
        $methods = $controller->getMethods(\ReflectionMethod::IS_PUBLIC);

        $actions = [];
        foreach ($methods ?: [] as $method) {
            $name = $method->getName();
            if (strripos($name, 'action') === 0 && $name !== 'actions') {
                $actions[] = self::getPrepareActionName($name);
            }
        }
        return $actions;
    }

    /**
     * action name refactoring
     * @param $name
     * @return bool|string
     */
    public static function getPrepareActionName($name)
    {
        $action = str_replace('action', '', $name);
        $action = preg_replace_callback('/[A-Z]/',
            function ($match) {
                return '-' . strtolower($match[0]);
            },
            $action
        );
        return substr($action, 1);
    }


    public static function getControllerName($name)
    {
        $name = ucfirst($name);
        $pos = strripos($name, '-');
        if ($pos) {
            $subName = substr($name, $pos);
            $name = substr($name, 0, $pos);
            $subName = str_replace('-', '', $subName);
            $subName = ucfirst($subName);
            $name .= $subName;
        }
        $name .= 'Controller';
        return $name;
    }


    public static function getActionsBySubDomains()
    {
        $cActions = [];
        foreach (self::find()->each() as $menu) {
            if ($menu->sub_domain && $menu->sub_domain != 'false') {
                $cActions[$menu->sub_domain] = $menu::getActionsForController($menu->sub_domain);
            }
        }
        return $cActions;
    }


    public static function getChildrenItems()
    {
        return self::find()->where(['!=', 'sub_domain', 'false'])->all();
    }

    public function jsonSerialize()
    {
        return array_merge($this->getAttributes(), [
            'actions' => self::getActionsForController($this->sub_domain)
        ]);
    }
}
