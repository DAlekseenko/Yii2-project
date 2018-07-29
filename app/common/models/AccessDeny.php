<?php

namespace common\models;
//require(__DIR__ . '/../../../vendor/yiisoft/yii2/db/ActiveRecord.php');

use Yii;
use \yii\db;

class AccessDeny extends \yii\db\ActiveRecord
{
    /*public $ip = null;
    public $action = null;
    public $is_temporary = null;*/

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'access_deny';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ip', 'action', 'is_temporary'], 'safe'],
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        Yii::info("IP $this->ip was blocked to action $this->action");
        parent::afterSave($insert, $changedAttributes);
    }


}