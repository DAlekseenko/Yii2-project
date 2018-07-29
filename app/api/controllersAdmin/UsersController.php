<?php

namespace api\controllersAdmin;

use api\models\admin\Users;
use api\models\exceptions\ModelException;
use common\components\services\builders\MoneyStatBuilder;
use common\components\services\builders\UsersReportBuilder;
use common\models\PushTask;
use common\components\services\Environment;
use common\models\Reports;
use yii;
use yii\web\HttpException;
use api\components\web\AdminApiController;
use api\models\admin\UsersForm;
use api\models\admin\search\UsersSearch;
use api\components\services\Subscription\SubscriberHandler;
use common\components\services\MqJobMessage;


class UsersController extends AdminApiController
{

    /**
     * Lists User models.
     * @return mixed
     */
    public function actionGet()
    {
        $searchModel = new UsersSearch();
        $post = Yii::$app->request->post('data');
        $rolesList = UsersForm::listRoles();

        $query = $searchModel->getQuery();
        $totalUsers = $query->count();
        $activeUsers = $searchModel->getQuery()->isReal()->count();

        if (isset($post) && (!$searchModel->load($post, '') || !$searchModel->validate())) {
            return [
                'listRoles' => $rolesList,
                'userTypes' => UsersSearch::$userTypes,
                'subscriptionTypes' => UsersSearch::$subscriptionTypes,
                'users' => [],
                'totalCount' => 0,
                'totalUsers' => $totalUsers,
                'activeUsers' => $activeUsers
            ];
        }

        $data = $searchModel->frontSearch($query);

        return [
            'listRoles' => $rolesList,
            'userTypes' => UsersSearch::$userTypes,
            'subscriptionTypes' => UsersSearch::$subscriptionTypes,
            'totalCount' => $data->count(),
            'users' => $data->all(),
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers
        ];
    }

    /**
     * @param integer $id
     * @return mixed
     */
    public function actionGetCurrentUser($id = null)
    {
        return [
            'listRoles' => UsersForm::listRoles(),
            'user' => $id ? $this->findModel($id) : [],
        ];
    }

    /**
	 * @todo Требует глубокой переработки
	 *
     * Creates or Update a User model.
     * @return mixed
     * @throws HttpException if the model cannot be load
     */
    public function actionEdit()
    {
        $post = Yii::$app->request->post('data');
        $id = isset($post['user_id']) ? $post['user_id'] : null;
        $data = Yii::$app->request->post('data');

        $model = $id ? $this->findModel($id) : false ?: new UsersForm(['scenario' => 'create']);

        if (!$model->checkAccess($data, $id)) {
            throw new HttpException(406);
        }

        if (!$model->createUser($data)) {
            throw new HttpException(400);
        }
        if ($model->hasErrors()) {
            return $this->returnFieldError($model);
        }
        return ['user' => $model];
    }

	/**
	 * @param  $id
	 * @return mixed
	 * @throws \Exception
	 * @throws \Throwable
	 */
    public function actionActivate($id)
    {
        return Yii::$app->db->transaction(function() use ($id) {
			$user = Users::find()->byId($id)->oneForUpdate();
			$user->setSubscriptionStatus($user::USER_TYPE_USER)->save();

			return ['user' => $user];
		});
    }

	/**
	 * @param  $id
	 * @return array|mixed
	 * @throws \Exception
	 * @throws \Throwable
	 */
    public function actionDeactivate($id)
    {
		return Yii::$app->db->transaction(function() use ($id) {
			$user = Users::find()->byId($id)->oneForUpdate();
			$user->setSubscriptionStatus($user::USER_TYPE_BLANK)->save();

			return ['user' => $user];
		});
    }

	/**
	 * @param $id
	 * @return mixed
	 * @throws \Exception
	 * @throws \Throwable
	 */
    public function actionEnableBan($id)
    {
		return Yii::$app->db->transaction(function() use ($id) {
			$user = Users::find()->byId($id)->oneForUpdate();
			$user->setBanned(true)->save();

			return ['user' => $user];
		});
    }

	/**
	 * @param $id
	 * @return mixed
	 * @throws \Exception
	 * @throws \Throwable
	 */
    public function actionDisableBan($id)
    {
		return Yii::$app->db->transaction(function() use ($id) {
			$user = Users::find()->byId($id)->oneForUpdate();
			$user->setBanned(false)->save();

			return ['user' => $user];
		});
    }

    public function actionGetSummary()
    {
        return [
            'charityCount' => Users::countCharityUsers(),
            'appCount' => Users::getActiveUsersQueryByMode(Environment::MODULE_APP)->count(),
            'ussdCount' => Users::getActiveUsersQueryByMode(Environment::MODULE_USSD)->count(),
            'webCount' => Users::getActiveUsersQueryByMode(Environment::MODULE_WEB)->count(),
        ];
    }

	/**
	 * @param  $id
	 * @return mixed
	 * @throws \Exception
	 * @throws \Throwable
	 */
    public function actionClear($id)
    {
		return Yii::$app->db->transaction(function() use ($id) {
			$handler = SubscriberHandler::createById($id);
			$handler->clearContract(false, false);
			return ['user' => $handler->getUser()];
		});
    }

	/**
	 * @param $id
	 * @return array
	 * @throws \Exception
	 * @throws \Throwable
	 */
    public function actionDisableSubscription($id)
    {
		return Yii::$app->db->transaction(function() use ($id) {
			$subscribe = SubscriberHandler::createById($id);
			if ($subscribe->unsubscribe()) {
				return ['user' => $subscribe->getUser()];
			}
			throw new \Exception('Unable to unsubscribe user');
		});
    }


    /**
     * Creates a new PushTask model.
     * @return mixed
     * @throws HttpException if the model cannot be load
     */
    public function actionRegisterPush()
    {
        $post = Yii::$app->request->post('data');
        $pushTask = new PushTask;
        $pushTask->leader_id = \Yii::$app->user->id;

        if ($pushTask->load($post['push'], '') && $pushTask->save()) {
            $searchModel = new UsersSearch();
            if (isset($post['filters'])) {
                $searchModel->load($post['filters'], '');
            }
            $serializedModel = base64_encode(serialize($searchModel));

            /** @var \common\components\services\MqConnector $connector */
            $connector = \Yii::$app->amqp;
            $connector->sendMessageDirectly(new MqJobMessage(yii::$app->params['jobsQueue'], 'prepare-push-task/from-query', [$pushTask->id, $serializedModel]));
            return true;
        }

        if ($pushTask->hasErrors()) {
            return $this->returnFieldError($pushTask);
        }
        throw new HttpException(400);
    }

    /**
     * Creates a new Reports model.
     * @return mixed
     * @throws HttpException if the model cannot be load
     */
    public function actionRegisterReport()
    {
        $searchModel = new UsersSearch();

        $data = Yii::$app->request->post('data');

        if (isset($data['query_string'])) {
            $searchModel->load($data['query_string'], '');
        }
        $reportMaker = new UsersReportBuilder($searchModel);

        $data['leader_id'] = \Yii::$app->user->id;
        $data['path'] = $reportMaker->getFilePath();
        $data['report_maker'] = base64_encode(serialize($reportMaker));

        $model = new Reports;
        if (isset($data) && $model->load($data, '') && $model->save()) {
            return $model;
        }
        if ($model->hasErrors()) {
            return $this->returnFieldError($model);
        }
        throw new HttpException(400);
    }

    /**
     * Creates a new Reports model.
     * @return mixed
     * @throws HttpException if the model cannot be load
     */
    public function actionGetMoneyStatistic()
    {
        $data = Yii::$app->request->post();

        $reportMaker = new MoneyStatBuilder($data);

        $data['leader_id'] = \Yii::$app->user->id;
        $data['path'] = $reportMaker->getFilePath();
        $data['file_name'] = $reportMaker->getName();
        $data['report_maker'] = base64_encode(serialize($reportMaker));

        $model = new Reports;
        if (isset($data) && $model->load($data, '') && $model->save()) {
            return $model;
        }
        throw new HttpException(400);

    }


    /**
     * @param integer $id
     * @return UsersForm the loaded model
     * @throws HttpException if the model cannot be load
     */
    protected function findModel($id)
    {
        if (($model = UsersForm::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(400);
    }

}