<?php
namespace api\controllers;

use api\components\formatters\EntitiesFormatter;
use common\models\PaymentFavoritesSearch;
use common\models\PaymentTransactionsSearch;
use frontend\models\virtual\AddFavorite;
use yii;
use frontend\models\virtual\SendMailForm;
use common\models\PaymentTransactionsHistory as PaymentTransactions;
use common\models\PaymentFavorites;
use common\models\InvoicesUsersData;
use common\models\Users;

class PaymentsController extends AbstractController
{
	public function behaviors()
	{
		return [
			'access' => [
				'class' => yii\filters\AccessControl::className(),
				'rules' => [
					[
						'roles' => [Users::ROLE_BANNED],
						'allow' => false,
					],
					[
						'actions' => ['send-invoice', 'history-item'],
						'allow' => true,
					],
					[
						'roles' => ['@'],
						'allow' => true,
					],
					[
						'allow' => false,
					],
				],
			],
		];
	}

	public function actionSendInvoice($recipient, $key)
	{
		$model = new SendMailForm();
		$model->email = $recipient;

		if (!$model->validate()) {
			return $this->returnFieldError($model);
		}

		$paymentHistoryItem = PaymentTransactions::find()->with('service')->andWhere(['status' => PaymentTransactions::STATUS_SUCCESS])->byKey($key)->one();
		if (empty($paymentHistoryItem)) {
			throw new yii\web\NotFoundHttpException('Квитанция не найдена');
		}

		return $model->sendPaymentsInvoice($paymentHistoryItem);
	}

	public function actionAddFavorite($key, $name)
	{
		$model = new AddFavorite();

		$model->name = $name;
		if (!$model->validate()) {
			return $this->returnFieldError($model);
		}

		$paymentHistoryItem = PaymentTransactions::find()->with('service')->byKey($key)->one();
		if (empty($paymentHistoryItem)) {
			throw new yii\web\NotFoundHttpException('Квитанция не найдена');
		}

		return $model->add($paymentHistoryItem, yii::$app->user->id);
	}

	public function actionUpdateFavorite($id, $name)
	{
		$favorite = PaymentFavorites::find()->currentUser()->byId($id)->one();
		if (empty($favorite)) {
			throw new yii\web\NotFoundHttpException('Пункт избранного не найден');
		}
		$favorite->name = $name;
		if (!$favorite->validate()) {
			return $this->returnFieldError($favorite);
		}

		return $favorite->update();
	}

	public function actionDelFavorite($id)
	{
		$favorite = PaymentFavorites::find()->currentUser()->byId($id)->one();
		if (empty($favorite)) {
			throw new yii\web\NotFoundHttpException('Пункт избранного не найден');
		}
		return $favorite->delete();
	}

	public function actionHistory($page = null, $per_page = null, $date_from = null, $date_to = null, $advanced = 0)
	{
		/** @todo перевести списки истории и изранного на апи */
		$config = [
			'per-page' => $per_page,
			'page' => $page,
			'filter' => ['dateFrom' => $date_from, 'dateTo' => $date_to]
		];

		$searchModel = new PaymentTransactionsSearch();
		$dateProvider = $searchModel->search($config);
		$result = $dateProvider->getModels();

		$paginator = $dateProvider->getPagination();

		return [
			'list' => EntitiesFormatter::transactionSetFormatter($result, (bool) (int) $advanced),
			'count' => $dateProvider->getCount(),
			'total' => $dateProvider->getTotalCount(),
			'pageCount' => $paginator->getPageCount(),
		];
	}

	public function actionFavorites($page = null, $per_page = null, $advanced = 0)
	{
		$config = [
			'per-page' => $per_page,
			'page' => $page,
		];
		$searchModel = new PaymentFavoritesSearch();
		$dateProvider = $searchModel->search($config);
		$result = $dateProvider->getModels();

		$paginator = $dateProvider->getPagination();

		return [
			'list' => EntitiesFormatter::favoriteSetFormatter($result, (bool) (int) $advanced),
			'count' => $dateProvider->getCount(),
			'total' => $dateProvider->getTotalCount(),
			'pageCount' => $paginator->getPageCount(),
		];
	}

	public function actionHistoryItem(array $transaction_uuids, $advanced = 0)
	{
		$pt = PaymentTransactions::tableName();
		$ud = InvoicesUsersData::tableName();

		$transactions = PaymentTransactions::find()
			->select("$pt.*, $ud.description as item_name")
			->with(['user','service', 'service.servicesInfo', 'service.category', 'service.category.categoriesInfo'])
			->where(["$pt.uuid" => $transaction_uuids])
			->withoutNew()
			->leftJoin($ud, "$pt.user_id = $ud.user_id AND $pt.service_id = $ud.service_id AND $ud.visible_type = " . InvoicesUsersData::VISIBILITY_USER . " AND trim(both '\"' from ($pt.fields->0->'value')::text) = $ud.identifier")
			->all();

		$list = [];
		foreach ($transactions as $transaction) {
			$list[] = EntitiesFormatter::transactionFormatter($transaction, true, (bool) $advanced, (bool) $advanced);
		}

		return [
			'list' => $list
		];
	}
}
