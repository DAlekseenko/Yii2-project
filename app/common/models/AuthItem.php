<?php

namespace common\models;

use api\models\admin\ActionsRole;
use  \yii\db\ActiveRecord;

/**
 * @property string $name
 * @property int $type
 * @property string $description
 * @property string $rule_name
 * @property string $data
 * @property integer $created_at
 * @property integer $updated_at
 * @property ActionsRole [] $roleMain
 * @property ActionsRole [] $availableActions
 */
class AuthItem extends ActiveRecord implements \JsonSerializable
{

    public function rules()
    {
        return [
            [['name', 'type', 'description'], 'required'],
            [['name'], 'string', 'max' => 50],
            [['description'], 'string', 'max' => 50],
        ];
    }
    public function attributeLabels()
    {
        return [
            'name' => 'Имя',
            'description' => 'Описание',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAvailableActions()
    {
        return $this->hasMany(ActionsRole::className(), ['role_name' => 'name']);
    }

    public function getPermissionsByRole()
    {
        if ($this->type == 1) {
            return \Yii::$app->authManager->getPermissionsByRole($this->name);
        }
    }

    public function getChildRoles()
    {
        if ($this->type == 1) {
            return \Yii::$app->authManager->getChildren($this->name);
        }
    }


    public static function getRoles()
    {
        return self::find()->where(['type' => 1])->orderBy('name')->all();
    }

    public static function getRules()
    {
        return self::find()->where(['type' => 2])->all();
    }

    public function setChildren($data)
    {
        $auth = \Yii::$app->authManager;
        $role = $auth->getRole($this->name);

        $auth->removeChildren($role);
        $availablePermission = isset($data['availablePermission']) ? $data['availablePermission'] : [];

        foreach (array_keys($availablePermission) as $permission) {
            $permission = $auth->getPermission($permission);
            $auth->addChild($role, $permission);
        }



        ActionsRole::deleteAll(['role_name' => $this->name]);
        $controllers = isset($data['availablePartitions']) ? $data['availablePartitions']['controllers'] : [];

        foreach ($controllers as $controller => $actions) {
            $actionsRole = new ActionsRole();
            $actionsRole->controller = $controller;
            $actionsRole->actions = array_keys($actions);
            $actionsRole->role_name = $this->name;
            $actionsRole->save();
        }
        return true;
    }

    public function jsonSerialize()
    {
        $atr = [
            'availablePermission' => $this->getPermissionsByRole(),
            'childRoles' => $this->getChildRoles(),
            'availablePartitions' => $this->availableActions
        ];
        return array_merge($this->getAttributes(), $atr);
    }
}