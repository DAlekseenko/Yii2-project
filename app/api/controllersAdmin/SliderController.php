<?php

namespace api\controllersAdmin;

use api\components\web\AdminApiController;
use yii;
use yii\web\HttpException;
use api\models\admin\AdminSlider as Slider;

class SliderController extends AdminApiController
{
    /**
     * Lists all AdminSlider models.
     * @return mixed
     */
    public function actionGet()
    {
        return Slider::find()->all();
    }

    /**
     * Creates or Update a AdminSlider model.
     * @return mixed
     * @throws HttpException if the model cannot be load
     */
    public function actionEdit()
    {
        $post = Yii::$app->request->post();

        $id = !empty($post['id']) ? $post['id'] : null;

        $model = $id ? $this->findModel($id) : false ?: new Slider;
        if (!$model->load($post, '')) {
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
     * Deletes an existing AdminSlider model.
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
     * @return Slider the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Slider::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(400);
    }
}
