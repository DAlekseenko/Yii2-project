<?php

namespace common\models;

use common\components\services\Dictionary;
use common\components\services\Environment;
use frontend\models\InvoicesIgnoreDefault;
use api\models\admin\virtual\AdminMenu;
use Yii;
use yii\web\IdentityInterface;
use common\components\services\PhoneService;
use api\components\services\Auth\DTO\AuthData;
use api\components\services\Auth\AuthDataConverter;

/**
 * Users model
 *
 * @property integer $user_id
 * @property string $phone
 * @property string $password
 * @property string $first_name
 * @property string $last_name
 * @property string $patronymic
 * @property string $auth_key
 * @property integer $date_create
 * @property integer $location_id
 * @property array $params
 * @property string $email
 * @property string $contract_id_date_change
 * @property string $subscriber_uuid
 * @property string $subscription_status
 *
 * @property AuthItem roles
 * @property UserPasswords userPassword
 * @property PaymentTransactionsHistory [] $transactions
 * @property Invoices[] invoices
 * @property UserDevices[] userDevices
 * @property InvoicesIgnoreDefault[] invoicesIgnoreDefault
 * @property InvoicesUsersData[] invoicesUsersData
 * @property PaymentFavorites[] paymentFavorites
 * @property Session[] session
 */
class Users extends AbstractModel implements IdentityInterface, \JsonSerializable
{
    const BALANCE_ALLOWED_REMAIN = 0.3;

    const AUTH_BY_COOKIE = 1;
    const AUTH_BY_TOKEN = 2;

    const ROLE_BANNED = 'banned-user';
    const ROLE_USER = 'user';
    const ROLE_ADMIN = 'admin';

    const USER_TYPE_BLANK = 0;
    const USER_TYPE_USER = 100;
    const USER_TYPE_UNSUBSCRIBES = 160;
    const USER_TYPE_SUBSCRIBER = 200;
    const USER_TYPE_BLOCKED = -100;
    const USER_TYPE_REQUEST = -200;

    const PARAMS_ON_CREATE_ENVIRONMENT = 'onCreateEnv';

	const SERIALIZE_MODE_WITH_ACCESS = 1;
	const SERIALIZE_MODE_SIMPLE = 2;

	public static $serializeMode = null;

	protected $authType;

    public $newPassword;

    protected $balance = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%users}}';
    }

    protected function setAuthByToken()
    {
        $this->authType = self::AUTH_BY_TOKEN;
    }

    protected function setAuthByCookie()
    {
        $this->authType = self::AUTH_BY_COOKIE;
    }

    public function isAuthByToken()
    {
        return $this->authType == self::AUTH_BY_TOKEN;
    }

    public function isAuthByCookie()
    {
        return $this->authType == self::AUTH_BY_COOKIE;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone'], 'required'],
            [['date_create'], 'safe'],
            [['phone'], 'string', 'max' => 15],
            [['password', 'newPassword'], 'string', 'max' => 60],
            [['first_name', 'last_name', 'patronymic', 'email'], 'string', 'max' => 32],
            [['phone'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'Идентификатор',
            'phone' => 'Телефон',
            'password' => 'Пароль',
            'first_name' => 'Имя',
            'last_name' => 'Фамилия',
            'patronymic' => 'Отчество',
            'date_create' => 'Дата создания',
            'newPassword' => 'Новый пароль'
        ];
    }

    public function findByUserId($userId)
	{
		return static::findOne(['user_id' => $userId]);
	}

    public function isReal()
    {
        return $this->subscription_status > self::USER_TYPE_BLANK;
    }

    public function isSubscriber()
    {
        return $this->subscription_status >= self::USER_TYPE_UNSUBSCRIBES;
    }

    public function isBlocked()
    {
        return $this->subscription_status < self::USER_TYPE_BLANK;
    }

    public function isBlank()
    {
        return $this->subscription_status == self::USER_TYPE_BLANK;
    }

    public function isRequest()
    {
        return $this->subscription_status == self::USER_TYPE_REQUEST;
    }

    public static function findByUuid($uuid)
    {
        return self::find()->where(['subscriber_uuid' => $uuid])->one();
    }

    /** @deprecated
	 *
	 * @param $phone
	 * @return mixed
	 * @throws \Exception
	 * @throws \Throwable
	 */
	public static function getRealUser($phone)
	{
		return Yii::$app->db->transaction(function() use ($phone) {
			$user = self::find()->byPhone($phone)->oneForUpdate();
			if (empty($user)) {
				throw new \Exception('Cant find user');
			}
			if ($user->isBlank()) {
				$user->subscription_status = self::USER_TYPE_USER;
			}
			$user->save();

			return $user;
		});
	}

    /**
     * Очищаем все данные пользователя. Актуально вызывать при изменении контракта.
     */
    public function clearUserResource()
    {
        foreach ($this->invoices ?: [] as $invoice) {
            $invoice->delete();
        }
        foreach ($this->userDevices ?: [] as $userDevice) {
            $userDevice->delete();
        }
        foreach ($this->invoicesIgnoreDefault ?: [] as $ignoreDefault) {
            $ignoreDefault->delete();
        }
        foreach ($this->invoicesUsersData ?: [] as $invoicesUsersData) {
            $invoicesUsersData->delete();
        }
        foreach ($this->paymentFavorites ?: [] as $favorite) {
            $favorite->delete();
        }
        // Удаляем все сессии пользователя:
        foreach ($this->session ?: [] as $session) {
            $session->delete();
        }
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        /** @var self $identity */
        $identity = static::findOne(['user_id' => $id]);
        if (!empty($identity)) {
            $identity->setAuthByCookie();
        }
        return $identity;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        if (strlen($token) == 36) {
        	return self::findMobileIdentity($token);
		}
		return self::findWebIdentity($token);
    }

    private static function findMobileIdentity($token)
	{
		$t = Users::tableName();
		$tAT = UserDevices::tableName();
		/** @var self $identity */
		$identity = static::find()->innerJoin($tAT, "$t.user_id = $tAT.user_id and $tAT.access_token = :token", ['token' => $token])->one();
		if (!empty($identity)) {
			$identity->setAuthByToken();
		}
		return $identity;
	}

	/**
	 * @param $token
	 * @return null|static
	 */
	private static function findWebIdentity($token)
	{
		try {
			$converter = new AuthDataConverter();
			$authData = $converter->decodeAuthData($token);
			$identity = static::findOne($authData->getUserId());
			if (!$identity || $identity->getAuthKey() !== $authData->getAuthKey()) {
				throw new \Exception('unable to identify user');
			}
			return $identity;
		} catch (\Exception $e) {
			return null;
		}
	}

	/**
	 * @param null $duration
	 * @return string
	 */
	public function getCryptAuthData($duration = null)
	{
		$authData = new AuthData($this->getId(), $this->getAuthKey(), $duration);
		$converter = new AuthDataConverter();

		return $converter->encodeAuthData($authData);
	}

    /**
     * Finds user by phone
     *
     * @param  string $phone
     * @return static|null|\common\models\Users
     */
    public static function findByPhone($phone)
    {
        return static::find()->where(['phone' => $phone])->one();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return md5('salt ^_^' . $this->getId() . '(.)_(.)' . $this->contract_id_date_change);
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Проверяет введенный пользователем пароль при логине. Это НЕ валидатор поля password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function checkPassword($password)
    {
        try {
            $result = $this->password && Yii::$app->security->validatePassword($password, $this->password);
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    public function setStatusUser()
    {
        $this->subscription_status = self::USER_TYPE_USER;
        return $this;
    }

    public function getUserNameFull()
    {
        return trim($this->last_name . ' ' . $this->first_name . ' ' . $this->patronymic, ' ');
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function getBalance()
    {
        if ($this->balance === null) {
            $this->balance = PhoneService::getBalance(self::preparePhone($this->phone));
        }
        return $this->balance;
    }

    /**
     * При оплате с карты мы ждем пополнения баланса для оплаты - это один процесс, поэтому нам нужно уметь очищать кеш
     * перед запросом.
     */
    public function clearBalanceCache()
    {
        $this->balance = null;

        return $this;
    }

    public function canPay($sum, &$text)
    {
        $availableBalance = bcsub($this->getBalance(), self::BALANCE_ALLOWED_REMAIN, 2);
        if (bccomp($availableBalance, $sum, 2) > -1) { // $availableBalance >= $sum
            return true;
        }
        $text = Dictionary::insufficientFunds();
        return false;
    }

    /**
     * @param $phone
     * @return string возвращает телефон с добавленным кодом или другими штучками что бы смс отправлялись
     */
    public static function preparePhone($phone)
    {
        return $phone;
    }

    /**
     * Generates password hash from password and sets it to the model
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserPassword()
    {
        return $this->hasOne(UserPasswords::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserDevices()
    {
        return $this->hasMany(UserDevices::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInvoices()
    {
        return $this->hasMany(Invoices::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInvoicesIgnoreDefault()
    {
        return $this->hasMany(InvoicesIgnoreDefault::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInvoicesUsersData()
    {
        return $this->hasMany(InvoicesUsersData::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentFavorites()
    {
        return $this->hasMany(PaymentFavorites::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSession()
    {
        return $this->hasMany(Session::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransactions()
    {
        return $this->hasMany(PaymentTransactionsHistory::className(), ['user_id' => 'user_id']);
    }

    /**
     * @inheritdoc
     * @return UsersQuery the active query used by this AR class.
     */
    public static function find($banned = false)
    {
        return new UsersQuery(get_called_class());
    }

    public function isBanned()
    {
        return $this->hasRole($this::ROLE_BANNED);
    }

    public function hasRole($role)
    {
        $roles = Yii::$app->authManager->getRolesByUser($this->user_id);
        return isset($roles[$role]);
    }

    public function getRoles()
    {
        return $this->hasMany(AuthItem::className(), ['name' => 'item_name'])
            ->viaTable('auth_assignment', ['user_id' => 'user_id']);
    }

    public function getUserFIO()
    {
        if ($this->last_name) {
            return $this->last_name . ' ' . mb_substr($this->first_name, 0, 1) . '. ' . mb_substr($this->patronymic, 0, 1) . '.';
        }
        return null;
    }

    public function setEnv(Environment $env)
	{
		if (isset($env)) {
			if (!isset($user->params['onCreateEnv'])) {
				$this->params = ['onCreateEnv' => $env];
			} else {
				$params = $this->params;
				$params['onCreateEnv']['prop'] = array_merge($env->getProps(), $params['onCreateEnv']['prop']);
				$this->params = $params;
			}
		}
	}

	/**
	 * @param bool $ban
	 * @return $this
	 * @throws \Exception
	 */
	public function setBanned($ban = TRUE)
	{
		$authManager = Yii::$app->authManager;
		$role = $authManager->getRole($this::ROLE_BANNED);

		if ($ban == true) {
			if ($this->subscription_status == self::USER_TYPE_USER) {
				$this->subscription_status = self::USER_TYPE_BLANK;
			}
			$authManager->assign($role, $this->user_id);
		} else {
			$authManager->revoke($role, $this->user_id);
		}

		return $this;
	}

	/**
	 * Кол-во успешных транывзакций
	 * @return mixed
	 */
	public function countSuccessTransactions()
	{
		return isset($this->transactions) ? count($this->transactions) : 0;
	}

	public function setSubscriptionStatus($status)
	{
		$this->subscription_status = $status;
		return $this;
	}

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            $authManager = Yii::$app->authManager;
            $role = $authManager->getRole($this::ROLE_USER);
            $authManager->assign($role, $this->getPrimaryKey());
        }
        parent::afterSave($insert, $changedAttributes);
    }

	public function countUserDevices()
	{
		$apple = $google = 0;
		/** @var $device UserDevices $device */
		foreach ($this->userDevices ?: [] as $device) {
			$device->device_type == $device::DEVICE_APPLE || strlen(trim($device->device_id)) == 36 ? $apple++ : $google++;
		}
		return [$apple, $google];
	}

	public function getAvailablePartitionsByRoles()
	{
		/** @var AuthItem $role */
		$rules = [];
		foreach ($this->roles as $role) {
			/** @var ActionsRole $act */
			if ($role->name == self::ROLE_ADMIN){
				$rules = AdminMenu::getActionsBySubDomains();
				break;
			}
			foreach ($role->availableActions as $act) {
				if (isset($rules[$act->controller])) {
					$rules[$act->controller] = array_merge($rules[$act->controller], $act->actions);
				} else {
					$rules[$act->controller] = $act->actions;
				}
			}
		}
		return $rules;
	}

	public function jsonSerialize()
	{
		$base = $this->getAttributes();
		unset($base['password']);
		$custom = [
			'banned' => $this->isBanned(),
		];
		if ($this::$serializeMode === null) {
			$custom['roles'] = $this->roles;
			$custom['successTransactions'] = $this->countSuccessTransactions();
			list($custom['apple'], $custom['google']) = $this->countUserDevices();
		}

		if ($this::$serializeMode == $this::SERIALIZE_MODE_WITH_ACCESS) {
			$custom = array_merge($custom, [
				'permission' => Yii::$app->authManager->getPermissionsByUser($this->getId()),
				'accessActions' => AdminMenu::getByUser($this),
				'accessRules' => $this->getAvailablePartitionsByRoles()
			]);
		}

		return array_merge($base, $custom);
	}
}