<?php

namespace api\models\admin;


use common\components\services\TemplateHelper;
use PbrLibBelCommon\Caller\WsCaller;
use Yii;

class PaymentTransactions extends \common\models\PaymentTransactions
{

    const SERIALIZE_MODE_FRONT_END = 3;

    public function reversal()
    {
        PaymentTransactions::$serializeMode = PaymentTransactions::SERIALIZE_MODE_FRONT_END;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$this->canReversal()) {
                throw new \Exception('Сторнировать можно только завершенные операции в течении 24 часов');
            }

            $this->status = self::STATUS_REVERSAL;
            if (!$this->update(false, ['status'])) {
                throw new \Exception('Ошибка обновления БД');
            }

            $paymentId = $this->getPaymentId();
            if (!$paymentId) {
                throw new \Exception('Нет paymentId');
            }

            $result = $this->tryReversal($paymentId);
            if (!$result) {
                throw new \Exception('Нет связи с processing-erip');
            } elseif (empty($result['success'])) {
                throw new \Exception(!empty($result['errors']) ? $result['errors'] : 'Ошибка на стороне processing-erip');
            }

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $this->addError('id', $e->getMessage());
            $transaction->rollBack();
            return false;
        }
    }

    public function canReversal()
    {
        if ($this->status !== self::STATUS_SUCCESS) return false;
        if (time() - strtotime($this->date_pay) > 24 * 60 * 60) return false;
        return true;
    }

    private function tryReversal($paymentId)
    {
        $getParams = TemplateHelper::fillTemplates(
            Yii::$app->params['reversalApi']['get'],
            ['serviceId' => $this->service_id, 'paymentId' => $paymentId]
        );
        $caller = new WsCaller(Yii::$app->params['reversalApi']['url']);
        $caller->bulkSetGetParameters($getParams);
        return $caller->callDecodeJson();
    }

    public function getUser()
    {
        return $this->hasOne(Users::className(), ['user_id' => 'user_id']);
    }
}