<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * Class PushTaskUsers
 * @package common\models
 * @property integer $id
 * @property integer $push_task_id
 * @property integer $user_id
 * @property boolean $processed
 */

class PushTaskUsers extends ActiveRecord
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['user_id' => 'user_id']);
    }
}