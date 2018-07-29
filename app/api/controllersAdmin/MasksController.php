<?php

namespace api\controllersAdmin;

use api\components\web\AdminApiController;
use yii;
use yii\web\HttpException;
use common\models\Masks;

class MasksController extends AdminApiController
{
    /**
     * Lists all Masks models.
     * @return mixed
     */
    public function actionGet()
    {
        return Masks::find()->all();
    }

    /**
     * Creates a new Masks model.
     * @return mixed
     * @throws HttpException if the model cannot be load
     */
    public function actionCreate()
    {
        $model = new Masks();
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
     * Updates an existing Masks model.
     * @return mixed
     * @throws HttpException if the model cannot be load
     */
    public function actionUpdate()
    {
        $id = Yii::$app->request->post('name');
        $model = $this->findModel($id);

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
     * Deletes an existing Masks model.
     * @return boolean
     * @throws HttpException if the model cannot be found
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
     * Finds the Documents model based on its primary key value.
     * If the model is not found, a 400 HTTP exception will be thrown.
     * @param string $id
     * @return Masks the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Masks::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(400);
    }
}
