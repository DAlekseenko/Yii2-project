<?php

namespace api\controllersAdmin;

use api\components\web\AdminApiController;
use yii;
use yii\web\HttpException;
use api\models\admin\AdminRecommendations as Recommendations;

class RecommendationsController extends AdminApiController
{
    /**
     * Lists all Recommendations models.
     * @throws HttpException if the model cannot be load
     * @return mixed
     */
    public function actionGet()
    {
        $data =  Recommendations::find()->all();
        if($data){
            return $data;
        }
        throw new HttpException(400);
    }

    /**
     * Creates a new Recommendations model.
     * @return mixed
     * @throws HttpException if the model cannot be load
     */
    public function actionCreate()
    {
        $model = new Recommendations();
        if (!$model->load(Yii::$app->request->post(), '')) {
            throw new HttpException(400);
        }
        if ($model->save()) {
            return $model;
        }
        if ($model->hasErrors()) {
            return $this->returnFieldError($model);
        }
        throw new HttpException(400);
    }

    /**
     * Deletes an existing Recommendations model.
     * @return bool
     * @throws HttpException
     * @throws \Exception
     * @throws yii\db\StaleObjectException
     */
    public function actionDelete()
    {
        $id = Yii::$app->request->post('id');
        if ($this->findModel($id)->delete()) {
            return true;
        }
        throw new HttpException(400);
    }

    /**
     * Finds the Recommendations model based on its primary key value.
     * If the model is not found, a 400 HTTP exception will be thrown.
     * @param string $id
     * @return Recommendations the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Recommendations::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(400);
    }
}