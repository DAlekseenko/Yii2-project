<?php

namespace api\controllersAdmin;

use Yii;
use common\models\Replacements;
use yii\web\HttpException;
use api\components\web\AdminApiController;

class ReplacementsController extends AdminApiController
{

    /**
     * Lists all Replacements models.
     * @return mixed
     */
    public function actionGet()
    {
        return [
            'targetLabels' => Replacements::$targets,
            'list' => Replacements::find()->all()
        ];
    }

    /**
     * Creates or Update a Replacements model.
     * @return mixed
     * @throws HttpException if the model cannot be load
     */
    public function actionEdit()
    {
        $post = Yii::$app->request->post();
        $id = isset($post['id']) ? $post['id'] : null;
        $post['use_regexp'] = (bool)$post['use_regexp'];
        $post['terminate'] = (bool)$post['terminate'];

        $model = $id ? $this->findModel($id) : false ?: new Replacements;

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
     * Deletes an existing Replacements model.
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
     * Check Replacements.
     * @return string
     */

    public function actionReplace()
    {
        $params = Yii::$app->request->post();
        return Replacements::apply($params['target'], $params['search']);
    }

    /**
     * Finds the Replacements model based on its primary key value.
     * If the model is not found, a 400 HTTP exception will be thrown.
     * @param integer $id
     * @return Replacements the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Replacements::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(400);
    }
}

