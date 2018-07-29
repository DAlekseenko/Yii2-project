<?php

namespace api\controllersAdmin;

use api\components\web\AdminApiController;
use common\models\Categories;
use common\models\Params;
use common\models\Services;
use common\models\ServicesCount;
use common\models\Locations;
use yii\web\HttpException;

class InstrumentsController extends AdminApiController
{
    public function actionClearCache()
    {
        try {
            \Yii::$app->cache->invalidateTags([
                Categories::tableName(),
                Services::tableName(),
                ServicesCount::tableName(),
                Locations::tableName()
            ]);
            return true;
        } catch (HttpException $e) {
            new HttpException(400, 'Ошибка при очистке Кэша');
        }
    }

    /**
     * Lists all Params models.
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionGetParams()
    {
        return Params::find()->orderBy('id')->all();
    }

    /**
     * Updates an existing Masks model.
     * @return mixed
     * @throws HttpException if the model cannot be load
     */
    public function actionSaveParams()
    {
        $data = \Yii::$app->request->post();

        $model = $this->findModel($data['id']);

        if ($model->setValue($data) && $model->save()) {
                return $model;
        }
        if ($model->hasErrors()) {
            return $this->returnFieldError($model);
        }
        throw new HttpException(400);
    }

    /**
     * Finds the Params model based on its primary key value.
     * If the model is not found, a 400 HTTP exception will be thrown.
     * @param string $id
     * @return Params the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Params::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(400);
    }

}