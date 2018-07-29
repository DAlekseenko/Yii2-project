<?php

namespace common\models;

use Yii;
use common\components\services\Helper;

/**
 * This is the model class for table "user_devices".
 *
 * @property integer $user_id
 * @property integer $device_id
 * @property string $password
 * @property string $access_token
 * @property string $at_date_create
 * @property string $send_code_date
 * @property string $api_token
 * @property string $device_type
 *
 * @property Users $user
 */
class UserDevices extends \yii\db\ActiveRecord
{
    const CODE_LIVE_INTERVAL = 3600;

    const DEVICE_GOOGLE = 'google';
    const DEVICE_APPLE = 'apple';

    protected $availableDevices = [
        self::DEVICE_GOOGLE => 1,
        self::DEVICE_APPLE => 1
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_devices';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'device_id'], 'required'],
            [['user_id'], 'integer'],
            [['device_id', 'access_token', 'api_token', 'device_type'], 'string'],
            [['device_type'], 'validateDeviceType'],
            [['password'], 'string', 'length' => 60],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'device_id' => 'Device ID',
            'password' => 'Password',
        ];
    }

    /**
     * @param $deviceId
     * @param $userId
     * @return UserDevices|null
     */
    public static function findDevice($deviceId, $userId)
    {
        return static::find()->where(['device_id' => $deviceId, 'user_id' => $userId])->one();
    }

    /**
     * @param  integer $deviceId
     * @param  Users $user
     * @return UserDevices|null
     */
    public static function getDevice($deviceId, Users $user)
    {
        $device = static::findDevice($deviceId, $user->user_id) ?: new static();

        $device->device_id = $deviceId;
        $device->user_id = $user->user_id;
        $device->access_token = static::generateAccessToken();
        $device->at_date_create = date('Y-m-d H:i:s');

        return $device->save() ? $device : null;
    }

    public static function deleteToken($deviceId, Users $user)
    {
        /** @var self $device */
        $device = static::findDevice($deviceId, $user->user_id);
        if (empty($device)) {
            return false;
        }
        $device->access_token = $device->at_date_create = $device->password = $device->send_code_date = null;

        return $device->save();
    }

    public static function findGooglePushTokensByUserId($userId)
    {
        return self::find()->select('api_token')
            ->where(['user_id' => $userId, 'device_type' => self::DEVICE_GOOGLE])
            ->andWhere('access_token IS NOT NULL')
            ->andWhere('api_token IS NOT NULL')->column();
    }

    public static function findApplePushTokensByUserId($userId)
    {
        return self::find()->select('api_token')
            ->where(['user_id' => $userId, 'device_type' => self::DEVICE_APPLE])
            ->andWhere('access_token IS NOT NULL')
            ->andWhere('api_token IS NOT NULL')->column();
    }

    public static function hasPush($userId)
    {
        return (bool)self::find()->where(['user_id' => $userId])
            ->andWhere('access_token IS NOT NULL')
            ->andWhere('api_token IS NOT NULL')
            ->andWhere('device_type IS NOT NULL')->count();
    }

    public static function hasPushByDevices($userId, $devices)
    {
        return (bool)self::find()->where(['user_id' => $userId])
            ->andWhere('access_token IS NOT NULL')
            ->andWhere('api_token IS NOT NULL')
            ->andWhere(['in', 'device_type', $devices])->count();
    }


    public static function clearApiTokens(array $tokens)
    {
        if (empty($tokens)) {
            return 0;
        }
        return self::updateAll(['api_token' => null], ['api_token' => $tokens]);
    }

    public static function generateAccessToken()
    {
        return Helper::createUuid();
    }

    public function validatePassword($password)
    {
        try {
            $result = $this->password && Yii::$app->security->validatePassword($password, $this->password);
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    public function setPassword($password)
    {
        $this->password = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['user_id' => 'user_id']);
    }

    public static function checkCode($access_token)
    {
        return (bool)self::find()
            ->where(['access_token' => $access_token])
            ->andWhere(['or', 'password IS NOT NULL', ['>', 'send_code_date', date('Y-m-d H:i:s', time() - self::CODE_LIVE_INTERVAL)]])
            ->count();
    }

    /**
     * @param $accessToken
     * @return self
     */
    public static function getDeviceWithCode($accessToken)
    {
        Yii::info('Getting device with access_token: ' . $accessToken, 'rest');
        return self::find()
            ->where(['access_token' => $accessToken])
            ->andWhere(['or', 'password IS NOT NULL', ['>', 'send_code_date', date('Y-m-d H:i:s', time() - self::CODE_LIVE_INTERVAL)]])
            ->one();
    }

    /**
     * @param $accessToken
     * @param $userId
     * @return self|null|\yii\db\ActiveRecord
     */
    public static function getUserDevice($accessToken, $userId)
    {
        return self::find()->where(['access_token' => $accessToken, 'user_id' => $userId])->one();
    }

    public function getDeviceType()
    {
        if (isset($this->device_type)) {
            return $this->device_type;
        }
        // Если устройство не указано, то пытаемся определить его косвенно по идентификатору девайса
        if (strlen($this->device_id) == 36) {
            return self::DEVICE_APPLE;
        }
        return self::DEVICE_GOOGLE; // пока у нас есть только apple и google
    }

    public function touchCode()
    {
        if (isset($this->send_code_date)) {
            $codeTime = strtotime($this->send_code_date);
            $dieDate = time() - self::CODE_LIVE_INTERVAL;
            if ($codeTime > $dieDate && $codeTime < $dieDate + 60 * 5) {
                $this->send_code_date = date('Y-m-d H:i:s', $codeTime + 60 * 10);
                return $this->save();
            }
            return false;
        }
    }

    public function validateDeviceType()
    {
        if (!isset($this->availableDevices[$this->device_type])) {
            $this->addError('device_type', 'Неизвестный тип устройства.');
        }
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->user_id = $this->user_id ?: Yii::$app->user->id;
        }
        return parent::beforeSave($insert);
    }
}