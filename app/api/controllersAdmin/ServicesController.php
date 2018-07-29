<?php

namespace api\controllersAdmin;

use Yii;
use api\components\web\AdminApiController;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;
use api\models\admin\search\ServicesSearch;
use api\models\admin\AdminServices as Services;
use common\models\Masks;

class ServicesController extends AdminApiController
{

    /**
     * Lists Service models.
     * @return mixed
     */
    public function actionGet()
    {
        $searchModel = new ServicesSearch();
        $post = Yii::$app->request->post('data');

		$query = $searchModel->getQuery();
		$entitiesCount = $query->count();

        if (isset($post) && (!$searchModel->load($post, '') || !$searchModel->validate())) {
            return [
                'services' => [],
                'totalCount' => 0,
				'entitiesCount' => $entitiesCount
            ];
        }

        $data = $searchModel->frontSearch($query);
        return [
            'totalCount' => $data->count(),
            'services' => $data->all(),
			'entitiesCount' => $entitiesCount
        ];
    }

    /**
     * Finds the current Service model
     * @param integer $id
     * @return mixed
     */
    public function actionGetCurrentService($id = null)
    {
        return [
            'masks'=> ArrayHelper::map(Masks::find()->all(), 'name', 'name'),
            'service' => $id ? $this->findModel($id) : []
        ];
    }

    /**
     * Updates an existing ServicesInfo model.
     * @return mixed
     * @throws HttpException if the model cannot be load
     */
    public function actionUpdate()
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
     *  Finds the Services model based on its primary key value.
     * If the model is not found, a 400 HTTP exception will be thrown.
	 * @param integer $id
	 * @return Services the loaded model
	 * @throws HttpException if the model cannot be found
	 */
	protected function findModel($id)
	{
		if (($model = Services::findOne($id)) !== null) {
			return $model;
		} else {
			throw new HttpException(400);
		}
	}
}


