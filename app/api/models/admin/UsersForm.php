<?php

namespace api\models\admin;

use Yii;

class UsersForm extends Users
{
    public $passwordRepeat;

    public function beforeValidate()
    {
        $this->phone = preg_replace('/[^0-9]/', '', $this->phone);
        return parent::beforeValidate();
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['password', 'passwordRepeat'], 'required', 'on' => 'create'];
        $rules[] = ['password', 'string', 'min' => 4, 'on' => 'create'];
        $rules[] = ['passwordRepeat', 'compare', 'compareAttribute' => 'password', 'on' => 'create'];
        return $rules;
    }

    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels['passwordRepeat'] = 'Повторите пароль';
        return $labels;
    }

    public function beforeSave($insert)
    {
        if ($insert && !empty($this->password)) {
            $this->setPassword($this->password);
        }
        return parent::beforeSave($insert);
    }

    public function checkAccess($data, $id)
    {
        $authManager = Yii::$app->authManager;

        if (!Yii::$app->user->can('UserEdit')) {
            $accessDeny = false;

            if (!$id && isset($data['roles'])) {
                $accessDeny = true;
            }

            if ($id) {
                $userRoles = $authManager->getRolesByUser($id) ?: [];
                $roles = isset($data['roles']) ? $data['roles'] : [];
                $accessDeny = array_keys($roles) !== array_keys($userRoles);
            }

            if ($data['password'] || $data['newPassword'] || $accessDeny) {
                return false;
            }
        }
        return true;
    }


    public function createUser($data)
    {
        $authManager = Yii::$app->authManager;
        $transaction = Yii::$app->getDb()->beginTransaction();

        try {
            $this->load($data, '');
            if(!$this->validate()) return true;
            $this->save();
            $userId = $this->user_id;
            $authManager->revokeAll($userId);
            $roles = isset($data['roles']) ? array_keys($data['roles']) : [];
            foreach ($roles as $role) {
                $currentRole = $authManager->getRole($role);
                $authManager->assign($currentRole, $userId);
            }
            $transaction->commit();
            return $this;
        } catch (\Exception $e) {
            $transaction->rollBack();
            return false;
        }
    }
}
