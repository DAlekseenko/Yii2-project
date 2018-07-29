<?php

namespace api\models\admin;

use api\components\services\Auth\AuthDataConverter;
use api\components\services\Auth\DTO\AuthData;
use Yii;
use common\models\PaymentTransactionsHistory;

class Users extends \common\models\Users
{
    const SALT = 'salt ^_^';


    public function beforeSave($insert)
    {
        if ($insert == false && !empty($this->newPassword)) {
            $this->setPassword($this->newPassword);
        }

        return parent::beforeSave($insert);
    }

    public static function listRoles()
    {
        return \yii\helpers\ArrayHelper::map(Yii::$app->authManager->getRoles(), 'name', 'description');
    }

    public function getAuthKey()
    {
        return md5(self::SALT . $this->getId() . '(.)_(.)' . date('z'));
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $converter = new AuthDataConverter();
    	$authData = $converter->decodeAuthData($token);
        $identity = static::findOne($authData->getUserId());
        if ($identity && $identity->getAuthKey() == $authData->getAuthKey()) {
            $identity->setAuthByToken();
            return $identity;
        }
        return null;
    }

    /**
     * Возвращает кол-во пользователей, которые пользователись благотворительностью без регистранции в сервисе.
     *
     * @return int
     */
    public static function countCharityUsers()
    {
        $u = self::tableName();
        $t = PaymentTransactionsHistory::tableName();

        return self::find()
            ->select("$u.user_id")
            ->joinWith('transactions')
            ->isBlank()
            ->andWhere("$u.params->'onCreateEnv'->'prop'->'target'='\"charity\"'")
            ->groupBy("$u.user_id")
            ->having(" count($t.id) > 0 ")->count();
    }

    public static function getActiveUsersQueryByMode($mode)
    {
        return self::find()->isReal()->andWhere("params->'onCreateEnv'->'name'='\"{$mode}\"' ");
    }
}
