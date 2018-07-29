<?php

namespace common\models;

use yii;
use yii\data\ActiveDataProvider;

/**
 * PaymentTransactionsSearch represents the model behind the search form about `common\models\PaymentTransactions`.
 */
class PaymentFavoritesSearch extends PaymentFavorites
{

	/**
	 * @param array $params
	 * @return ActiveDataProvider
	 */
	public function search($params)
	{
		$query = PaymentFavorites::find()->currentUser()->with('service.category.categoriesInfo')->orderBy('name');

		$config = [
			'query' => $query,
			'pagination' => [
				'page' => isset($params['page']) ? $params['page'] - 1 : 0,
				'pageSize' => isset($params['per-page']) ? $params['per-page'] : 10000,
			],
		];

		return new ActiveDataProvider($config);
	}
}