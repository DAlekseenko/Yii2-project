<?php

namespace api\controllersAdmin;

use api\components\amqp\InternalQueue;
use common\models\PaymentTransactionsHistory;
use common\components\services\builders\PaymentsReportBuilder;
use common\models\Reports;
use yii;
use api\components\web\AdminApiController;
use api\models\admin\search\PaymentReportSearch;
use api\models\admin\PaymentTransactions;
use yii\web\HttpException;

class PaymentReportController extends AdminApiController
{
    /**
     * Lists PaymentTransactionsHistory models.
     * @return mixed
     */
    public function actionGet()
    {
        $post = Yii::$app->request->post('data') ?: [];
        $searchModel = new PaymentReportSearch($post);
        $offset = isset($post['offset']) ? $post['offset'] : 0;
        $totalTransaction = $searchModel->getQuery()->count();
        $searchModel->applyFilters();
        $data = $searchModel->applyOrders()->getQuery()->limit(100)->offset($offset);

        return [
            'list' => $data->all(),
            'totalCount' => $data->count(),
            'totalTransaction' => $totalTransaction,
            'startID' => PaymentTransactionsHistory::getLastID()
        ];
    }

    /**
     * Find Model PaymentTransactions
     * @param integer $id
     * @return mixed
     */
    public function actionGetPaymentInfo($id)
    {
        return ['paymentInfo' => $this->findModel($id)];
    }

    /**
     * Reversal PaymentTransactions Model
     * @return mixed
     * @throws HttpException
     */
    public function actionReversal()
    {
        $id = Yii::$app->request->post('id');
        if (($model = PaymentTransactions::findOne($id)) !== null) {

            if ($model->reversal()) {
                return $model;
            }

            if ($model->hasErrors()) {
                return $this->returnFieldError($model);
            }
            throw new HttpException(400);
        }
        throw new HttpException(400);
    }

    /**
     * Creates a new Reports model.
     * @return mixed
     * @throws HttpException if the model cannot be load
     */
    public function actionRegisterReport()
    {
        $data = Yii::$app->request->post('data');

        $query_string = isset($data['query_string']) ? $data['query_string'] : null;
        $searchModel = new PaymentReportSearch($query_string);

        $ruleReportView = Yii::$app->user->can('ReportView');

        $reportMaker = new PaymentsReportBuilder($searchModel,$ruleReportView);

        $data['leader_id'] = \Yii::$app->user->id;
        $data['path'] = $reportMaker->getFilePath();
        $data['report_maker'] = base64_encode(serialize($reportMaker));

        $model = new Reports();
        if (isset($data) && $model->load($data, '') && $model->save()) {
            return $model;
        }
        if ($model->hasErrors()) {
            return $this->returnFieldError($model);
        }
        throw new HttpException(400);
    }

    /**
     * @param integer $id
     * @return PaymentTransactionsHistory the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        PaymentTransactions::$serializeMode = PaymentTransactions::SERIALIZE_MODE_FRONT_END;
        if (($model = PaymentTransactionsHistory::findOne($id)) !== null) {
            return $model;
        } else {
            throw new HttpException(400);
        }
    }
}
