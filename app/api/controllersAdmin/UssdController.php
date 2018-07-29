<?php

namespace api\controllersAdmin;

use Yii;
use common\models\Ussd;
use yii\web\HttpException;
use api\components\web\AdminApiController;

class UssdController extends AdminApiController
{

    /**
     * Lists all Ussd models.
     * @return mixed
     */
    public function actionGet()
    {
        return [
            'plugNames' => Ussd::PLUG_NAMES,
            'list' => Ussd::find()->orderBy(['plug' => SORT_ASC, 'code' => SORT_ASC])->all()
        ];
    }

    /**
     * Creates or Ussd Update a Replacements model.
     * @return mixed
     * @throws HttpException if the model cannot be load
     */
    public function actionEdit()
    {
        $post = Yii::$app->request->post();
        $id = isset($post['Ussd']['id']) ? $post['Ussd']['id'] : null;
        $model = $id ? $this->findModel($id) : false ?: new Ussd;

        if (!$model->load($post)) {
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
     * Deletes an existing Ussd model.
     * @return bool
     * @throws HttpException
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
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
     * Finds the Ussd model based on its primary key value.
     * If the model is not found, a 400 HTTP exception will be thrown.
     * @param integer $id
     * @return Ussd the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        list($plug, $code) = explode("-", $id);
        if (($model = Ussd::findOne(['plug' => $plug, 'code' => $code])) !== null) {
            return $model;
        }
        throw new HttpException(400);
    }
}

