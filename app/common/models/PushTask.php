<?php

namespace common\models;

use yii\db\ActiveRecord;
use yii\web\HttpException;

/**
 * Class PushTask
 * @package common\models
 * @property integer $id
 * @property integer $leader_id
 * @property string $title
 * @property string $text
 * @property boolean $apple
 * @property boolean $android
 * @property \DateTime $date_create
 * @property \DateTime $date_concluding
 * @property integer $status
 *
 * @property  Users $user
 */
class PushTask extends ActiveRecord implements \JsonSerializable
{
    const STATUS_REGISTER  = 0;
	const STATUS_PREPARED  = 1;
    const STATUS_PERFORMED = 2;
    const STATUS_SUCCESS   = 3;
    const STATUS_FAILURE   = -1;

    const statusList = [
        self::STATUS_REGISTER  => 'Зарегистрирован',
		self::STATUS_PREPARED  => 'Подготовлен',
        self::STATUS_PERFORMED => 'Выполняется',
        self::STATUS_SUCCESS   => 'Выполнен успешно',
        self::STATUS_FAILURE   => 'Выполнен не успешно',
    ];

    public function rules()
    {
        return [
            [['title', 'text', 'leader_id'], 'required'],
            [['title'], 'string', 'max' => 50],
            [['text'], 'string', 'max' => 100],
        ];
    }

	/**
	 * @param $id
	 * @return array|null|ActiveRecord|self
	 */
    public static function findById($id)
	{
    	return self::find()->where(['id' => $id])->one();
	}

    public function attributeLabels()
    {
        return [
            'title' => 'Заголовок',
            'text' => 'Текст'
        ];
    }

    public function load($data, $formName = null)
    {
        $this->apple = !empty($data['apple']);
        $this->android = !empty($data['android']);
        return parent::load($data, $formName);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPushTaskUsers()
    {
        return $this->hasMany(PushTaskUsers::className(), ['push_task_id' => 'id']);
    }

    public function getPushTaskUsersCount()
    {
        return $this->getPushTaskUsers()->count();
    }

    /**
     * @param $query UsersQuery
     * @return UsersQuery
     */
    public function getNecessaryUsers(UsersQuery $query)
    {
        $users = $query->select('users.user_id')->joinWith('userDevices');

        if ($this->apple && $this->android) {
            $users->andWhere(['or',
                ['user_devices.device_type' => 'apple'],
                ['user_devices.device_type' => 'google']
            ]);
        } else {
            if ($this->apple) {
                $users->andWhere(['user_devices.device_type' => 'apple']);
            }
            if ($this->android) {
                $users->andWhere(['user_devices.device_type' => 'google']);
            }
        }

        $query->andWhere("user_devices.api_token IS NOT NULL");
        $query->groupBy('users.user_id');

        return $users;
    }

	/**
	 * @param UsersQuery $users
	 * @return bool
	 * @throws \Exception
	 * @throws \yii\db\Exception
	 */
    public function setPushUsersTask(UsersQuery $users)
    {
        $transaction = \Yii::$app->getDb()->beginTransaction();

        try {
            foreach ($users->each() as $user) {
                $roles = \Yii::$app->authManager->getRolesByUser($user->id);
                if (isset($roles['banned-user'])) {
                    continue;
                }
                $pushUserTask = new PushTaskUsers();
                $pushUserTask->push_task_id = $this->id;
                $pushUserTask->user_id = $user->id;
                $pushUserTask->processed = 0;
                $pushUserTask->save();
            }
            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['user_id' => 'leader_id']);
    }

    public function jsonSerialize()
    {
        return array_merge(
            $this->getAttributes(),
            [
                'user' => $this->user->last_name . ' ' . $this->user->last_name,
                'pushTaskUsers' => $this->getPushTaskUsersCount()
            ]
        );
    }
}