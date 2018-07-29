<?php

namespace api\controllersAdmin;

use common\models\DealersSubscribers;
use yii;
use yii\web\HttpException;
use api\components\web\AdminApiController;
use common\models\DealersEmployees;
use common\components\services\PhoneService;

class DealersController extends AdminApiController
{

    /**
     * Lists all DealersEmployees.
     * @return mixed
     */
    public function actionGet()
    {
        $login = Yii::$app->user->identity->phone;
        $post = \Yii::$app->request->post();
        $offset = isset($post['offset']) ? $post['offset'] : 0;
        $employee = DealersEmployees::getEmployeeByLogin($login) ?: null;
        $data = (new DealersEmployees)->getSortedData($offset, $employee);
        return [
            'list' => $data->all(),
            'login' => $login,
            'totalCount' => $data->count(),
        ];
    }

    /**
     * Creates a new DealersSubscriber model or finds the DealersSubscriber model based on abonent_phone.
     * If the model is not found, a 400 HTTP exception will be thrown.
     * @return mixed
     * @throws HttpException if the model cannot be load
     */
    public function actionAdd()
    {
        $model = new DealersSubscribers;

        if ($model->load(\Yii::$app->request->post(), '') && $model->save()) {
            $text = 'Уважаемый абонент! Сервис МТС Деньги – это информирование о начислениях и все платежи без комиссии, с мобильного и банковских карт. Для регистрации перейдите на http://dengi.mts.by/app';
            /** TODO рвскомитеть перед релизом */
            //PhoneService::sendAbstractSms($model->subscriber_phone, $text);
            return true;
        }

        if ($model->hasErrors()) {
            return $this->returnFieldError($model);
        }

        throw new HttpException(400);
    }

}