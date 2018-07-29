<?php

namespace api\controllersAdmin;

use api\components\web\AdminApiController;
use yii;
use yii\web\HttpException;
use common\models\Documents;

class DocumentsController extends AdminApiController
{
    /**
     * Lists all Documents models.
     * @return mixed
     */
    public function actionGet()
    {
        return Documents::find()->all();
    }

    /**
     * Creates a new Documents model.
     * @return mixed
     * @throws HttpException if the model cannot be load
     */
    public function actionCreate()
    {
        $model = new Documents();
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
     * Updates an existing Documents model.
     * @return mixed
     * @throws HttpException if the model cannot be load
     */
    public function actionUpdate()
    {
        $id = Yii::$app->request->post('id');
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
     * Publish an existing Documents model.
     * @return mixed
     * @throws HttpException if the model cannot be found
     */
    public function actionPublish()
    {
        $id = Yii::$app->request->post('id');
        $model = $this->findModel($id);
        $model->text = $model->draft;
        $model->draft = '';
        if ($model->save()) {
            return $model;
        }
        throw new HttpException(400);
    }

    /**
     * Restore an existing Documents model.
     * @return mixed
     * @throws HttpException if the model cannot be found
     */
    public function actionRestore()
    {
        $id = Yii::$app->request->post('id');
        $model = $this->findModel($id);
        $model->draft = $model->text;
        if ($model->save()) {
            return $model;
        }
        throw new HttpException(400);
    }

    /**
     * Deletes an existing Documents model.
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
     * @return Documents the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Documents::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(400);
    }
}
