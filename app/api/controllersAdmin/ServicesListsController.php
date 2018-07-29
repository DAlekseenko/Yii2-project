<?php

namespace api\controllersAdmin;

use api\components\web\AdminApiController;
use common\models\ServicesLists;
use api\models\admin\search\ServicesListsSearch;
use yii\web\HttpException;
use yii;

class ServicesListsController extends AdminApiController
{
    /**
     * Lists ServicesLists models.
     * @return mixed
     */
    public function actionGet()
    {
        $searchModel = new ServicesListsSearch();
        $data = $searchModel->search(Yii::$app->request->post('data') ?: [])->orderBy('list_name');
        return [
            'listLabels' => ServicesLists::$listLabels,
            'list' => $data->all(),
            'totalCount' => $searchModel->countAll()
        ];
    }

    /**
     * Creates a new ServicesLists model.
     * @return mixed
     * @throws HttpException if the model cannot be load
     */
    public function actionCreate()
    {
        $model = new ServicesLists();

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
     * Delete an existing  ServicesLists models.
     * @return boolean
     * @throws HttpException if the model cannot be load
     */
    public function actionDelete()
    {
        $key = Yii::$app->request->post('id');
        $key = explode("-", $key);
        if (ServicesLists::findOne(['list_name' => $key[0], 'service_id' => $key[1]])->delete()) {
            return true;
        }
        throw new HttpException(400);
    }
}