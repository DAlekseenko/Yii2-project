<?php

namespace api\controllersAdmin;

use yii;
use common\models\CategoriesCustom;
use api\components\web\AdminApiController;
use common\models\CategoryCustomCategories;
use common\models\CategoryCustomServices;

use yii\web\HttpException;
use api\components\formatters\EntitiesFormatter;

class CategoriesCustomController extends AdminApiController
{
    /**
     * Lists all CategoriesCustom models.
     * @return mixed
     */
    public function actionGet()
    {
        return CategoriesCustom::find()->orderBy('name')->all();
    }

    /**
     * Update a CategoriesCustom model.
     * @return mixed
     * @throws HttpException if the model cannot be load
     */
    public function actionEdit()
    {
        $post = Yii::$app->request->post();
        $id = isset($post['id']) ? $post['id'] : null;

        $model = $id ? $this->findModel($id) : false ?: new CategoriesCustom;

        if (!$model->load($post, '')) {
            throw new HttpException(400);
        }
        if ($model->save() && $model->saveIcons($post)) {
            return $model;
        }
        if ($model->hasErrors()) {
            return $this->returnFieldError($model);
        }
        throw new HttpException(400);
    }

    /**
     * Deletes an existing CategoriesCustom model.
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
     * Finds the Replacements model based on its primary key value.
     * If the model is not found, a 400 HTTP exception will be thrown.
     * @param integer $id
     * @return CategoriesCustom the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CategoriesCustom::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(400);
    }

    /**
     * Creates related a CategoriesCustom model CategoryCustomCategories.
     * @return mixed
     * @throws HttpException if the model cannot be found
     */
    public function actionAddCategoryAndGetList()
    {
        $post = Yii::$app->request->post();
        $id = $post['custom_category_id'];

        $newBind = new CategoryCustomCategories();
        if (!$newBind->load($post, '')) {
            throw new HttpException(400);
        }
        if ($newBind->save()) {
            return EntitiesFormatter::categorySetFormatter($this->findModel($id)->categories ?: []);
        }
        if ($newBind->hasErrors()) {
            return $this->returnFieldError($newBind);
        }
        throw new HttpException(400);
    }

    /**
     * Deletes related a CategoriesCustom model CategoryCustomCategories.
     * @return mixed
     * @throws HttpException if the model cannot be found
     */
    public function actionUnbindCategoryAndGetList()
    {
        $id = Yii::$app->request->post('relId');
        $u_key = Yii::$app->request->post('id');

        $link = CategoryCustomCategories::getLink($id, $u_key);
        if ($link->delete()) {
            return EntitiesFormatter::categorySetFormatter($this->findModel($id)->categories ?: []);
        }
        throw new HttpException(400);
    }

    /**
     * Creates related a CategoriesCustom model CategoryCustomServices.
     * @return mixed
     * @throws HttpException if the model cannot be found
     */
    public function actionAddServiceAndGetList()
    {
        $post = Yii::$app->request->post();
        $id = $post['custom_category_id'];

        $newBind = new CategoryCustomServices();

        if (!$newBind->load($post, '')) {
            throw new HttpException(400);
        }
        if ($newBind->save()) {
            return EntitiesFormatter::serviceSetFormatter($this->findModel($id)->services ?: []);
        }
        if ($newBind->hasErrors()) {
            return $this->returnFieldError($newBind);
        }
        throw new HttpException(400);
    }

    /**
     * Deletes related a CategoriesCustom model CategoryCustomServices.
     * @return mixed
     * @throws HttpException if the model cannot be found
     */
    public function actionUnbindServiceAndGetList()
    {
        $id = Yii::$app->request->post('relId');
        $u_key = Yii::$app->request->post('id');

        $link = CategoryCustomServices::getLink($id, $u_key);

        if ($link->delete()) {
            return EntitiesFormatter::serviceSetFormatter($this->findModel($id)->services ?: []);
        }
        throw new HttpException(400);
    }

}
