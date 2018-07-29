<?php
namespace frontend\controllersAbstract;

use common\models\InvoicesUsersData;
use common\models\MainCategories;
use common\models\PaymentFavoritesSearch;
use common\models\PaymentTransactionsHistory as PaymentTransactions;
use common\models\PaymentFavorites;
use yii\web\NotFoundHttpException;
use common\models\PaymentTransactionsSearch;
use common\models\Categories;
use common\models\Locations;
use common\models\Services;
use common\models\ServicesCount;
use frontend\models\Users;
use frontend\models\virtual\AddFavorite;
use frontend\components\behaviors\AjaxEmptyLayout;
use frontend\models\virtual\SendMailForm;
use yii\filters\AccessControl;
use yii;
use common\models\ServicesLists;

class PaymentsController extends AbstractController
{

	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::className(),
				'except' => ['index', 'pay', 'history-item', 'send-invoice', 'refresh-status'],
				'rules' => [
					[
						'roles' => [Users::ROLE_BANNED],
						'allow' => false,
					],
					[
						'allow' => true,
						'roles' => ['@'],
					],
					[
						'allow' => false,
					],
				],
			],
			'ajaxEmptyLayout' => [
				'class' => AjaxEmptyLayout::className(),
			],
		];
	}

	public function actionIndex()
	{
		return $this->render('index', self::getIndexParams());
	}

	public function actionHistory()
	{
		$searchModel = new PaymentTransactionsSearch();
		$dataProvider = $searchModel->search(Yii::$app->request->queryParams);

		return $this->render('history', [
			'dataProvider' => $dataProvider,
			'searchModel'  => $searchModel,
			'addFavorite'  => new AddFavorite(),
		]);
	}

	public function actionPay($id)
	{
		$service = Services::findById((int) $id, true);
		if (!$service) {
			throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
		}

		$request = \yii::$app->request;
		$favId = $request->get('favId');
		$invoiceId = $request->get('invoice');

		$favorite    = isset($favId)     ? PaymentFavorites::find()->currentUser()->byId($favId)->one() : null;
		$invoiceData = isset($invoiceId) ? InvoicesUsersData::find()->with('service')->currentUser()->byId($invoiceId)->one() : null;

		$fieldValues = $request->get('default', []);

		$fields = null;
		if (!empty($fieldValues)) {
			$fields = $fieldValues;
		} elseif (!empty($favorite)) {
			$fields = $favorite->fields;
		} elseif (!empty($invoiceData)) {
			$fields = [$invoiceData->identifier];
		}
		$hasAccept = $service->isInList([ServicesLists::LIST_ACCEPT_SERVICES]);

		return $this->render('pay', ['service' => $service, 'fields' => json_encode($fields), 'hasAccept' => $hasAccept]);
	}

	public function actionFavorites()
	{
		$searchModel = new PaymentFavoritesSearch();
		$dataProvider = $searchModel->search(Yii::$app->request->queryParams);

		return $this->render('favorites', [
			'dataProvider' => $dataProvider,
		]);
	}

	/** @todo перенести в модель */
	//Возвращает массив параметров для действия index
	public static function getIndexParams($location_id = null)
	{
		$key = 'mainPageParams' . implode('-', Locations::getCurrentLocationTreeIds($location_id));
		$params = Yii::$app->cache->get($key);
		if ($params === false) {
			$params =  MainCategories::getMain($location_id);
			$dependency = [Categories::tableName(), Services::tableName(), ServicesCount::tableName()];
			Yii::$app->cache->setWithDependency($key, $params, $dependency);
		}
		return $params;
	}

	public function actionHistoryItem($id)
	{
		$userIdShowAuthButton = Yii::$app->session->get('userIdShowAuthButton');
		if (Yii::$app->user->isGuest && !$userIdShowAuthButton) {
			return $this->redirect(['/payments/history']);
		}

		$pt = PaymentTransactions::tableName();
		$ud = InvoicesUsersData::tableName();
		$paymentHistoryItem = PaymentTransactions::find()
			->select("$pt.*, $ud.description as item_name")->with('service.servicesInfo')->withoutNew()->byKey($id)
			->andWhere(['in', "$pt.user_id", [$userIdShowAuthButton, Yii::$app->user->id]])
			->leftJoin($ud, "$pt.user_id = $ud.user_id AND $pt.service_id = $ud.service_id AND $ud.visible_type = " . InvoicesUsersData::VISIBILITY_USER . " AND trim(both '\"' from ($pt.fields->0->'value')::text) = $ud.identifier")
			->one();

		if (!$paymentHistoryItem) {
			return $this->redirect(['/payments/history']);
		}

		if ($userIdShowAuthButton && Yii::$app->user->isGuest) {
			Yii::$app->session->set('userIdLoginByClick', $userIdShowAuthButton);
			$user = Users::findOne($userIdShowAuthButton);
		}

		return $this->render('historyItem', [
			'paymentHistoryItem' => $paymentHistoryItem,
			'sendMailForm' => new SendMailForm(),
			'addFavorite' => new AddFavorite(),
			'userShowLoginButton' => !empty($user) ? $user : false
		]);
	}

	public function actionRefreshHistoryItem($id)
	{
		$userIdShowAuthButton = Yii::$app->session->get('userIdShowAuthButton');
		if (Yii::$app->user->isGuest && !$userIdShowAuthButton) {
			throw new yii\web\NotAcceptableHttpException();
		}

		$pt = PaymentTransactions::tableName();
		$ud = InvoicesUsersData::tableName();
		$paymentHistoryItem = PaymentTransactions::find()
			->select("$pt.*, $ud.description as item_name")
			->with('service.servicesInfo')->withoutNew()->byKey($id)
			->andWhere(['in', "$pt.user_id", [$userIdShowAuthButton, Yii::$app->user->id]])
			->leftJoin($ud, "$pt.user_id = $ud.user_id AND $pt.service_id = $ud.service_id AND $ud.visible_type = " . InvoicesUsersData::VISIBILITY_USER . " AND trim(both '\"' from ($pt.fields->0->'value')::text) = $ud.identifier")
			->one();

		if (!$paymentHistoryItem) {
			throw new yii\web\NotFoundHttpException();
		}

		return $this->renderPartial('_historyItem', ['paymentHistoryItem' => $paymentHistoryItem, 'sendMailForm' => new SendMailForm()]);
	}

	public function actionHistoryItems(array $ids)
	{
		$pt = PaymentTransactions::tableName();
		$ud = InvoicesUsersData::tableName();
		$paymentHistoryItems = PaymentTransactions::find()
			->select("$pt.*, $ud.description as item_name")
			->currentUser()->with(['service.location', 'service.servicesInfo', 'user'])->byId($ids)->withoutNew()
			->leftJoin($ud, "$pt.user_id = $ud.user_id AND $pt.service_id = $ud.service_id AND $ud.visible_type = " . InvoicesUsersData::VISIBILITY_USER . " AND trim(both '\"' from ($pt.fields->0->'value')::text) = $ud.identifier")
			->all();

		if (!$paymentHistoryItems) {
			return $this->redirect(['/payments/history']);
		}

		return $this->render('historyItems', [
			'paymentHistoryItems' => $paymentHistoryItems,
			'sendMailForm' => new SendMailForm(),
			'addFavorite' => new AddFavorite(),
		]);
	}

	public function actionUpdateFavorite($id = null)
	{
		$favorite = PaymentFavorites::find()->currentUser()->byId($id)->one();

		return $this->render('favoriteItem', [
			'favoriteItem' => $favorite,
		]);
	}
}
