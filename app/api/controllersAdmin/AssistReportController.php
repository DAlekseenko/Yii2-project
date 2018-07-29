<?php

namespace api\controllersAdmin;

use api\components\amqp\InternalQueue;
use api\components\web\AdminApiController;
use api\models\admin\AdminAssistTransactions;
use api\models\admin\search\AssistTransactionSearch;
use common\components\services\builders\AssistReportBuilder;
use common\models\Reports;
use yii\web\HttpException;
use yii;


class AssistReportController extends AdminApiController
{
    /**
     * Lists AssistTransactions models.
     * @return mixed
     */
    public function actionGet()
    {
        $searchModel = new AssistTransactionSearch;
        $assist = new AdminAssistTransactions();
        $post = Yii::$app->request->post('data');

        $query = $searchModel->getQuery();

        $data = [
            'statusList' => $assist::statusList,
            'typesList' => $assist::typesList,
            'totalTransaction' => $query->count(),
            'lastDate' => $assist::getLastDateCreate(),
            'totalCount' => 0,
            'list' => [],
        ];

        if (isset($post) && (!$searchModel->load($post, '') || !$searchModel->validate())) {
            return $data;
        }

        $query = $searchModel->frontSearch($query);
        $data['totalCount'] = $query->count();
        $data['list'] = $query->all();

        return $data;

    }

    /**
     * Find Model PaymentTransactions
     * @param integer $id
     * @return mixed
     */
    public function actionGetAssistPaymentInfo($id)
    {
        $assist = new AdminAssistTransactions();
        return [
            'assistPaymentInfo' => $this->findModel($id),
            'statusList' => $assist::statusList,
            'typesList' => $assist::typesList
        ];
    }

    /**
     * Creates a new Reports model.
     * @return mixed
     * @throws HttpException if the model cannot be load
     */
    public function actionRegisterReport()
    {
        $searchModel = new AssistTransactionSearch;

        $data = Yii::$app->request->post('data');

        if (isset($data['query_string'])) $searchModel->load($data['query_string'], '');

        $reportMaker = new AssistReportBuilder($searchModel);

        $data['leader_id'] = \Yii::$app->user->id;
        $data['path'] = $reportMaker->getFilePath();
        $data['report_maker'] = base64_encode(serialize($reportMaker));

        $reportCreator = new Reports;

        if (isset($data) && $reportCreator->load($data, '') && $reportCreator->save()) {
            return $reportCreator;
        }
        if ($reportCreator->hasErrors()) {
            return $this->returnFieldError($reportCreator);
        }
        throw new HttpException(400);
    }

    /**
     * @param string $order_number
     * @return AdminAssistTransactions the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($order_number)
    {
        if (($model = AdminAssistTransactions::findOne(['order_number' => $order_number])) !== null) {
            return $model;
        } else {
            throw new HttpException(400);
        }
    }


}