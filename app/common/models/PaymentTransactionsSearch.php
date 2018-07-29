<?php

namespace common\models;

use yii;
use yii\data\ActiveDataProvider;

/**
 * PaymentTransactionsSearch represents the model behind the search form about `common\models\PaymentTransactions`.
 */
class PaymentTransactionsSearch extends PaymentTransactionsHistory
{
	const PAGE_SIZES = [
		10 => 10,
		30 => 30,
		50 => 50,
		100 => 100,
	];

	protected $params = [];

	public $dateFrom;
	public $dateTo;

	public function rules()
	{
		return [
			[['dateFrom', 'dateTo'], 'date'],
		];
	}

	/**
	 * @param array $params
	 * @return ActiveDataProvider
	 */
	public function search($params)
	{
		$pt = PaymentTransactionsHistory::tableName();
		$ud = InvoicesUsersData::tableName();

		$this->params = $params;
		$query = PaymentTransactionsHistory::find()
			->select("$pt.*, $ud.description as item_name")
			->with(['user','service', 'service.servicesInfo', 'service.category', 'service.category.categoriesInfo'])
			->orderBy("$pt.date_create desc")->with('service')
			->currentUser()
			->withoutNew()
			->leftJoin($ud, "$pt.user_id = $ud.user_id AND $pt.service_id = $ud.service_id AND $ud.visible_type = " . InvoicesUsersData::VISIBILITY_USER . " AND trim(both '\"' from ($pt.fields->0->'value')::text) = $ud.identifier");

		$config = [
			'query' => $query,
			'pagination' => [
				'page' => $this->getPage(),
				'pageSize' => $this->getPageSize(),
			],
		];
		$dataProvider = new ActiveDataProvider($config);

		$this->load($params, 'filter');

		$contractIdDateCreate = yii::$app->user->identity->contract_id_date_change;
		$filterDateStamp = strtotime($this->dateFrom);
		$contractDateStamp = strtotime($contractIdDateCreate);

		$dateFrom = $this->dateFrom;
		if (empty($filterDateStamp) || $contractDateStamp > $filterDateStamp) {
			$dateFrom = $contractIdDateCreate;
		}

		if (!$this->validate()) {
			return $dataProvider;
		}

		if ($dateFrom && $this->dateTo) {
			$filter = [
				'between',
				"$pt.date_create",
				date('Y-m-d H:i:s', strtotime($dateFrom)),
				date('Y-m-d 23:59', strtotime($this->dateTo)),
			];
			$query->andFilterWhere($filter);
		} elseif ($dateFrom) {
			$query->andFilterWhere(['>=', "$pt.date_create", date('Y-m-d H:i:s', strtotime($dateFrom))]);
		} elseif ($this->dateTo) {
			$query->andFilterWhere(['<=', "$pt.date_create", date('Y-m-d 23:59', strtotime($this->dateTo))]);
		}

		return $dataProvider;
	}

	public function getPage()
	{
		return isset($this->params['page']) ? $this->params['page'] - 1 : 0;
	}

	public function getPageSize()
	{
		$pageSize = isset($this->params['per-page']) ? $this->params['per-page'] : null;
		if (empty($pageSize)) {
			//дефолт - первый из списка
			return array_keys($this::PAGE_SIZES)[0];
		}

		return $pageSize;
	}
}