<?php

namespace api\controllersAdmin;

use common\models\MainCategoryLinks;
use yii;
use yii\web\HttpException;
use common\models\MainCategories;
use api\components\web\AdminApiController;

class MainPageController extends AdminApiController
{

    /**
     * Lists all MainCategories models.
     * @return mixed
     */
    public function actionGet()
    {
        return MainCategories::find()->orderBy('c_order')->all();
    }

    /**
     * Creates or Update a MainCategories model.
     * @return mixed
     * @throws HttpException if the model cannot be load
     */
    public function actionEdit()
    {
        $post = Yii::$app->request->post();

        $id = !empty($post['id']) ? $post['id'] : null;
        $model = $id ? $this->findModel($id) : false ?: new MainCategories;
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
     * Deletes an existing MainCategories model.
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
     * Creates related a MainCategories model MainCategoryLinks.
     * @return mixed
     * @throws HttpException if the model cannot be found
     */
    public function actionAddBindingAndGetList()
    {
        $post = Yii::$app->request->post();
        $id = $post['main_category_id'];

        $newBind = new MainCategoryLinks();
        if (!$newBind->load($post, '')) {
            throw new HttpException(400);
        }
        if ($newBind->save()) {
            return $this->findModel($id);
        }
        if ($newBind->hasErrors()) {
            return $this->returnFieldError($newBind);
        }
        throw new HttpException(400);
    }

    /**
     * Deletes related a MainCategories model MainCategoryLinks.
     * @return mixed
     * @throws HttpException if the model cannot be found
     */
    public function actionUnbindAndGetList()
    {
        $manCatID = Yii::$app->request->post('relId');
        $linkId = Yii::$app->request->post('id');

        $link = MainCategoryLinks::findOne(['main_category_id' => $manCatID, 'id' => $linkId]);
        if ($link->delete()) {
            return $this->findModel($manCatID);
        }
        throw new HttpException(400);
    }

    /**
     * Replace state related a MainCategories model MainCategoryLinks.
     * @return mixed
     * @throws HttpException if the model cannot be found
     */
    public function actionReplaceBinding()
    {
        $post = Yii::$app->request->post();
        $id = $post['id'];
        $links = $post['list'];
        try {
            $i = 0;
            foreach ($links as $link) {
                $newBind = MainCategoryLinks::findOne($link['link_id']);
                $newBind->order = $i++;
                $newBind->save();
            }
            return $this->findModel($id);
        } catch (\Exception $e) {
            throw new HttpException(400);
        }
    }

    /**
     * Finds the MainCategories model based on its primary key value.
     * If the model is not found, a 400 HTTP exception will be thrown.
     * @param string $id
     * @return MainCategories the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MainCategories::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(400);
    }

}

