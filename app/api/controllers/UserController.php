<?php

namespace api\controllers;

use api\components\services\Subscription\RegistrationService;
use api\components\services\Subscription\SubscriberHandler;
use common\components\services\ModelManager;
use common\models\Documents;
use common\models\PaymentTransactions;
use common\models\Users;
use common\models\virtual\ApiRegistration;
use frontend\models\virtual\InvoicesMobileForm;
use frontend\models\virtual\RechargeBalanceForm;
use yii;
use api\models\virtual\DevicePassword;
use api\models\virtual\CodeLogin;
use common\models\Locations;
use common\models\UserDevices;
use common\components\services\PhoneService;
use common\components\services\Helper;
use common\components\filters\RateLimitByKey;
use common\components\services\Dictionary;
use common\models\AssistTransactions;
use frontend\models\virtual\SettingsChangePassword;
use common\models\virtual\Login;
use yii\web\HttpException;

class UserController extends AbstractController
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['create-token', 'send-password', 'get-sdp-url', 'get-rules', 'get-agreement', 'send-registration-code', 'login'],
                        'allow' => true,
                    ],
                    [
                        'roles' => [Users::ROLE_BANNED],
                        'allow' => false,
                    ],
                    [
                        'roles' => ['@'],
                        'allow' => true,
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
            //к методу можно обратиться не чаще 3 раз в 15 минут(примерно). Неудачное обращение тоже считается.
            //одинаковыми считаются обращения к одному и тому же действию с одим и тем же phone
            'rateLimiter' => [
                'class' => yii\filters\RateLimiter::className(),
                'only' => ['create-token', 'send-password'],
                'user' => Yii::createObject([
                    'class' => RateLimitByKey::className(),
                    'key' => preg_replace('/[^0-9]/', '', Yii::$app->request->get('phone')),
                ]),
                'enableRateLimitHeaders' => false,
            ],
            'rateLimiter2' => [
                'class' => yii\filters\RateLimiter::className(),
                'only' => ['check-device-password'],
                'user' => Yii::createObject(['class' => RateLimitByKey::className(), 'key' => trim(Yii::$app->request->get('device_id'))]),
                'enableRateLimitHeaders' => false,
            ],
        ];
    }

    public function actionTotalLogout()
    {
        try {
            $user = yii::$app->getUser();
            /** @var Users $identity */
            $identity = $user->getIdentity();

            // Удаляем связку с девайсами
            foreach ($identity->userDevices ?: [] as $userDevice) {
                $userDevice->delete();
            }

            // Удаляем все сессии пользователя:
            foreach ($identity->session ?: [] as $session) {
                $session->delete();
            }
            $identity->password = null;
            $identity->save();

            if ($identity->isAuthByCookie()) {
                $user->logout();
            }
            yii::info("User {$identity->user_id} press total-logout");

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionDelSubscription()
    {
        /** @var Users $user */
        $user = yii::$app->user->identity;
        try {
            return Yii::$app->db->transaction(function () use ($user) {
                $subscriberHandler = SubscriberHandler::createByUserUuid($user->subscriber_uuid);
                return $subscriberHandler->unsubscribe();
            });
        } catch (\Exception $e) {
            return false;
        }
    }

    //обновление токена по phone и password(пароль постоянный либо временный)
    public function actionCreateToken($device_id, $phone, $password)
    {
        $model = new CodeLogin();
        $model->phone = $phone;
        $model->password = $password;
        if (!$model->login()) {
            return $this->returnFieldError($model);
        }
        $device = UserDevices::getDevice($device_id, $model->getUser());
        if (empty($device)) {
            throw new yii\web\ServerErrorHttpException('Ошибка при генерации ключа доступа');
        }
        return ['token' => $device->access_token, 'phone' => $model->phone];
    }

    //удаление токена....по токену xD
    public function actionDeleteToken($device_id)
    {
        if (!UserDevices::deleteToken($device_id, Yii::$app->user->identity)) {
            throw new yii\web\ServerErrorHttpException('Ошибка при удалении ключа доступа');
        }
        return true;
    }

    /**
     * @return array|bool
     * @throws \Throwable
     */
    public function actionSendRegistrationCode()
    {
        /** @var \common\components\services\Environment $env */
        $env = Yii::$app->environment;

        $model = new ApiRegistration();
        $registrationService = new RegistrationService($model);

        if ($registrationService->validate(Yii::$app->request->get()) === false) {
            return $this->returnFieldError($model);
        }
        $user = $registrationService->registerUser($env);
        if ($user === false) {
            return $this->returnFieldError($model);
        }
        if ($registrationService->sendRegistrationCode($user) === false) {
            return $this->returnFieldError($model);
        }
        return true;
    }

    /**
     * @param null $location_id
     * @return array|bool
     * @throws HttpException
     */
    public function actionLogin($location_id = null)
    {
        $model = new Login();
        if (!$model->load(Yii::$app->request->get(), '')) {
            throw new HttpException(400);
        }
        if ($model->validate()) {
            $user = Users::findByPhone($model->phone);
            $user::$serializeMode = Users::SERIALIZE_MODE_SIMPLE;
            if ($user->isBlank()) {
                $user->subscription_status = $user::USER_TYPE_USER;
            }
            if (is_null($user->location_id) && !empty(Locations::findById($location_id))) {
                $user->location_id = $location_id;
            }
            $user->save();

            return [
                'user' => $user,
                'authKey' => $user->getCryptAuthData()
            ];
        }
        if ($model->hasErrors()) {
            return $this->returnFieldError($model);
        }
        return false;
    }

    public function actionGet()
    {
        /** @var Users $user */
        $user = yii::$app->user->identity;
        $user::$serializeMode = $user::SERIALIZE_MODE_SIMPLE;

        return $user;
    }

    //отправить sms на телефон с временным паролем по номеру телефона
    /** @todo этот метод мы дергаем только из приложения. Таким образом определяем, что пользователь зарегился из приложения. */
    // выпелить когда появятся партнеры и продукты.
    public function actionSendPassword()
    {
        $request = yii::$app->request;
        /** @var \common\components\services\Environment $env */
        $env = Yii::$app->environment;
        $env->setName($env::MODULE_APP);

        if ($request->get('REMOTE_ADDR') !== null) {
            $env->setProp(['ip' => trim($request->get('REMOTE_ADDR'))]);
        }

        $model = new ApiRegistration();
        $registrationService = new RegistrationService($model);

        if ($registrationService->validate(Yii::$app->request->get()) === false) {
            return $this->returnFieldError($model);
        }
        $user = $registrationService->registerUser($env);
        if ($user === false) {
            return $this->returnFieldError($model);
        }
        if ($registrationService->sendRegistrationCode($user) === false) {
            return $this->returnFieldError($model);
        }
        return true;
    }

    //проверить что password(пароль устройства) соответствует device_id. Для доступа к методу необходима авторизация
    public function actionCheckDevicePassword()
    {
        $model = new DevicePassword();
        $model->setAttributes(Yii::$app->request->get());
        if (!$model->check()) {
            return $this->returnFieldError($model);
        }
        return true;
    }

    public function actionSetPushData($device_id, $token = null, $device_type = null)
    {
        $device = UserDevices::findDevice($device_id, yii::$app->user->id);
        if (empty($device)) {
            throw new yii\web\NotFoundHttpException('Устройство не найдено');
        }
        $device->api_token = $token;
        if (isset($device_type)) {
            $device->device_type = $device_type;
        }
        if ($device->save() === false) {
            return $this->returnFieldError($device);
        }
        return true;
    }

    //установить пароль устройства по device_id. Для доступа к методу необходима авторизация
    public function actionSetDevicePassword()
    {
        $model = new DevicePassword();
        $model->setAttributes(Yii::$app->request->get());
        if (!$model->resetPassword()) {
            return $this->returnFieldError($model);
        }
        return true;
    }

    public function actionGetSdpUrl($connection_id, $answer_to, $device_id)
    {
        return Helper::makeSdpUrl($connection_id, $answer_to, $device_id);
    }

    public function actionGetBalance()
    {
        $balance = Yii::$app->user->identity->getBalance();
        if ($balance === false) {
            throw new yii\web\ServerErrorHttpException('Ошибка при получении баланса');
        }
        return $balance;
    }

    public function actionCanPayInvoices()
    {
        $ids = yii::$app->request->post('ids', []);
        $invoicesForm = new InvoicesMobileForm(['ids' => $ids]);

        /** Если баланса достаточно, то пропускаем на оплату и ничего не делаем */
        if ($invoicesForm->validate()) {
            return true;
        }
        /** Если баланса не достаточно, то смотрим разницу */
        $balance = Yii::$app->user->identity->getBalance();
        $totalSum = $invoicesForm->getTotalSum();

        $need = $totalSum + Users::BALANCE_ALLOWED_REMAIN;
        if ($need - $balance <= RechargeBalanceForm::MAX_PAY) {
            return true;
        }
        throw new yii\web\ServerErrorHttpException(Dictionary::insufficientFunds());
    }

    public function actionRechargeRequirement(array $transaction_uuids)
    {
        /** @var Users $user */
        $user = Yii::$app->user->identity;
        $subscriberHandler = SubscriberHandler::createByUser($user);
        /* @todo костыль, чтобы заставить пользователя обновить апп */
        if (\yii::$app->request->get('version') < 1.3 && $subscriberHandler->isSubscriptionRequired()) {
            yii::$app->response->setResponseMessage('Для проведения платежа необходимо обновить приложение');
            throw new \Exception('update app');

        }
        /* end */

        $subscription = $subscriberHandler->getUserSubscriptionInfo();

        $transactions = PaymentTransactions::find()->currentUser()->onlyNew()->andWhere(['uuid' => $transaction_uuids])->all();
        $activeUuids = yii\helpers\ArrayHelper::getColumn($transactions, 'uuid');
        foreach ($transaction_uuids as $uuid) {
            if (!in_array($uuid, $activeUuids)) {
                throw new yii\web\NotFoundHttpException("Transaction with uuid $uuid was not found!");
            }
        }
        $rechargeBalanceForm = new RechargeBalanceForm();
        $modelManager = new ModelManager($rechargeBalanceForm);
        $canPay = $rechargeBalanceForm->canPayTransactions($user, $transactions);
        if ($canPay === false && $rechargeBalanceForm->hasErrors()) {
            return $this->returnFieldError($rechargeBalanceForm);
        }
        $teaser = $rechargeBalanceForm->getRechargeTeaser();
        return [
            'direct_pay' => empty($teaser),
            'model' => empty($teaser) ? [] : [
                'fields' => $modelManager->getFieldProperties()
            ],
            'teaser' => $teaser,
            'transactions' => $activeUuids,
            'subscription' => $subscription
        ];
    }

    public function actionGetRechargeModel()
    {
        $modelManager = new ModelManager(new RechargeBalanceForm());

        return [
            'fields' => $modelManager->getFieldProperties()
        ];
    }

    public function actionSendCode()
    {
        $code = rand(1000, 9999);
        PhoneService::sendPayContinueCode(Yii::$app->user->identity->phone, $code);

        return $code;
    }

    public function actionCodeOk()
    {
        if (Yii::$app->user->identity->isAuthByToken()) {
            /** @var UserDevices $userDevices */
            $userDevices = UserDevices::find()->where(['access_token' => Yii::$app->request->get('access_token')])->one();
            $userDevices->send_code_date = date('Y-m-d H:i:s');
            return $userDevices->save();
        }
        return false;
    }

    /**
     * @param null|string $first_name
     * @param null|string $last_name
     * @param null|string $patronymic
     * @return mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionSetProfile($first_name = null, $last_name = null, $patronymic = null)
    {
        $id = yii::$app->user->identity->getId();

        return Yii::$app->db->transaction(function () use ($id, $first_name, $last_name, $patronymic) {
            $user = Users::find()->byId($id)->oneForUpdate();
            if (isset($first_name)) {
                $user->first_name = $first_name;
            }
            if (isset($last_name)) {
                $user->last_name = $last_name;
            }
            if (isset($patronymic)) {
                $user->patronymic = $patronymic;
            }

            if (!$user->validate()) {
                return $this->returnFieldError($user);
            }
            return $user->save();
        });
    }

    /**
     * @param null $password
     * @param null $passwordRepeat
     * @return mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionChangePassword($password = null, $passwordRepeat = null)
    {
        return Yii::$app->db->transaction(function () use ($password, $passwordRepeat) {
            $model = new SettingsChangePassword();

            if (isset($password)) {
                $model->password = $password;
            }
            if (isset($passwordRepeat)) {
                $model->passwordRepeat = $passwordRepeat;
            }
            if (!$model->validate()) {
                return $this->returnFieldError($model);
            }
            if ($model->changePassword()) {
                return ['result' => 'success'];
            }
        });

    }


    public function actionGetProfile()
    {
        /** @var Users $user */
        $user = yii::$app->user->identity;

        $regionId = $cityId = $region = $city = null;
        if ($user->location_id) {
            $location = Locations::findById($user->location_id);
            if (!empty($location)) {
                if (empty($location->parent_id)) {
                    $regionId = $location->id;
                    $region = $location->name;
                } else {
                    $cityId = $location->id;
                    $city = $location->name;
                    $regionId = $location->parents(1)->one()->id;
                    $region = $location->parents(1)->one()->name;
                }
            }
        }
        $subscriberHandler = SubscriberHandler::createByUser($user);

        return [
            'phone' => $user->phone,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'patronymic' => $user->patronymic,
            'region' => $region,
            'city' => $city,
            'region_id' => $regionId,
            'city_id' => $cityId,
            'subscription' => $subscriberHandler->getUserSubscriptionInfo()
        ];
    }

    public function actionGetSubscriptionInfo()
    {
        /** @var Users $user */
        $user = yii::$app->user->identity;
        $subscriberHandler = SubscriberHandler::createByUser($user);

        return $subscriberHandler->getUserSubscriptionInfo();
    }

    public function actionGetRechargeInfo($order_number)
    {
        /** @var AssistTransactions $assistTransaction */
        $assistTransaction = AssistTransactions::find()->where(['user_id' => yii::$app->user->identity->user_id, 'order_number' => $order_number])->one();
        if (!isset($assistTransaction->assist_data)) {
            throw new yii\web\NotFoundHttpException();
        }
        $link = EXTERNAL_URL;
        $payServicesResult = null;
        $withServices = false;

        // Есть услуги, которые должны быть оплачены при успешном пополнении:
        if ($assistTransaction->status = AssistTransactions::STATUS_APPROVED && !empty($assistTransaction->data)) {
            $withServices = true;
            $transactionsCount = PaymentTransactions::find()->where(['user_id' => yii::$app->user->identity->user_id, 'id' => $assistTransaction->data])->withoutNew()->count();

            // Если услуги оплачены или добавлены в процесс:
            if ($transactionsCount == count($assistTransaction->data)) {
                $link .= count($assistTransaction->data) == 1
                    ? 'payments/history-item?id=' . $assistTransaction->data[0]
                    : 'payments/history-items?' . http_build_query(['ids' => $assistTransaction->data]);
                $payServicesResult = true;
            } else {
                if (time() - strtotime($assistTransaction->date_pay) > 60 * 5 + 30) {
                    $payServicesResult = false;
                } else {
                    throw new yii\web\NotFoundHttpException();
                }
            }
        }

        return [
            'code' => $assistTransaction->getPaymentInfo(),
            'redirect' => $link,
            'pay_services_result' => $payServicesResult,
            'with_services' => $withServices
        ];
    }

    public function actionRecharge($sum, $email = null, $first_name = null, $last_name = null, array $transaction_uuids = [])
    {
        /** @var \common\components\services\Environment $env */
        $env = Yii::$app->environment;
        $env->setName($env::MODULE_APP);
        $request = yii::$app->request;

        if ($request->get('REMOTE_ADDR') !== null) {
            $env->setProp(['ip' => trim($request->get('REMOTE_ADDR'))]);
        }

        $rechargeBalanceForm = new RechargeBalanceForm();

        $type = AssistTransactions::TYPE_RECHARGE;
        $transactionIds = null;
        $rechargeBalanceForm->sum = $sum;
        if (!empty($email)) {
            $rechargeBalanceForm->email = $email;
        }
        if (!empty($first_name)) {
            $rechargeBalanceForm->first_name = $first_name;
        }
        if (!empty($last_name)) {
            $rechargeBalanceForm->last_name = $last_name;
        }
        if (!empty($transaction_uuids)) {
            /** @var PaymentTransactions $tr */
            foreach (PaymentTransactions::find()->currentUser()->onlyNew()->andWhere(['uuid' => $transaction_uuids])->all() ?: [] as $tr) {
                $type = AssistTransactions::TYPE_PAYMENT;
                if (isset($tr->invoice)) {
                    $type = AssistTransactions::TYPE_INVOICE;
                }
                $transactionIds[] = $tr->id;
            }
        }

        if ($rechargeBalanceForm->validate() === false) {
            return $this->returnFieldError($rechargeBalanceForm);
        }
        if ($rechargeBalanceForm->save($type, $transactionIds, $env) === false) {
            throw new yii\web\HttpException(500);
        }
        return $rechargeBalanceForm->getAssistRequestPostFields();
    }

    public function actionGetRules()
    {
        return Documents::findByKey(Documents::KEY_RULES)->text;
    }

    public function actionGetAgreement()
    {
        return $this->renderPartial('@frontend/views/partial/help/part/_agreement');
    }
}
