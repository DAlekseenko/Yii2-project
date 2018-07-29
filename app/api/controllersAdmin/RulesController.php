<?php

namespace api\controllersAdmin;

use api\components\web\AdminApiController;
use yii;
use yii\web\HttpException;
use api\models\admin\virtual\AdminMenu;
use common\models\AuthItem;

class RulesController extends AdminApiController
{

    /**
     * Get all SystemRoles
     * @return mixed
     */
    public function actionGet()
    {
        return [
            'roles' => AuthItem::getRoles(),
            'permission' => AuthItem::getRules(),
            'main' => AdminMenu::getChildrenItems()
        ];
    }

    /**
     * Deletes an existing Roles.
     * @return boolean
     * @throws HttpException if the model cannot be found
     */
    public function actionDelete()
    {
        $name = Yii::$app->request->post('id');
        $role = Yii::$app->authManager->getRole($name);
        if (Yii::$app->authManager->remove($role)) {
            return true;
        }
        throw new HttpException(400);
    }


    /**
     * Create or Update a user group rule.
     * @return mixed
     * @throws HttpException if the model cannot be load
     */
    public function actionEdit()
    {
        $data = Yii::$app->request->post('data');

        $name = isset($data['name']) ? $data['name'] : null;

        if (($authItem = $this->findModel($name)) == false) {
            $authItem = new AuthItem;
            $data['type'] = 1;
        }

        if ($authItem->load($data, '') && $authItem->save()) {
            $authItem->setChildren($data);
            return $authItem;
        }

        if ($authItem->hasErrors()) {
            return $this->returnFieldError($authItem);
        }

        throw new HttpException(400);
    }


    /**
     * Finds the AuthItem model based on its primary key value.
     * If the model is not found, a 400 HTTP exception will be thrown.
     * @param string $name
     * @return mixed
     */
    protected function findModel($name)
    {
        if (($model = AuthItem::findOne($name)) !== null) {
            return $model;
        }
        return false;
    }
}
