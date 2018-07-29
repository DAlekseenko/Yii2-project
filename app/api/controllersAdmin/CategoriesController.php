<?php

namespace api\controllersAdmin;

use api\components\web\AdminApiController;
use yii;
use yii\web\HttpException;
use api\models\admin\search\CategoriesSearch;
use api\models\admin\AdminCategories as Categories;

class CategoriesController extends AdminApiController
{
    /**
     * Lists Categories models.
     * @return mixed
     */
    public function actionGet()
    {
        $searchModel = new CategoriesSearch();
        $post = Yii::$app->request->post('data');

        $query = $searchModel->getQuery();
        $entitiesCount = $query->count();


        if (isset($post) && (!$searchModel->load($post, '') || !$searchModel->validate())) {
            return [
                'categories' => [],
                'totalCount' => 0,
                'entitiesCount' => $entitiesCount
            ];
        }
        $data = $searchModel->frontSearch($query);
        return [
            'totalCount' => $data->count(),
            'categories' => $data->all(),
            'entitiesCount' => $entitiesCount
        ];
    }

    /**
     * Finds the current Categories model
     * @param integer $id
     * @return mixed
     */
    public function actionGetCurrentCategory($id = null)
    {
        return [
            'category' => $id ? $this->findModel($id) : [],
        ];
    }

    /**
     * Updates an existing CategoriesInfo model.
     * @return mixed
     * @throws HttpException if the model cannot be load
     */
    public function actionEdit()
    {
        $post = Yii::$app->request->post('data');
        $id = isset($post['id']) ? $post['id'] : null;

        $model = $this->findModel($id);

        if ($model->saveInfo($post)) {
            return $model;
        }

        if ($model->hasErrors()) {
            return $this->returnFieldError($model);
        }
        throw new HttpException(400);
    }


    /**
     * Finds the Categories model based on its primary key value.
     * If the model is not found, a 400 HTTP exception will be thrown.
     * @param string $id
     * @return Categories the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Categories::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(400);
    }
}
