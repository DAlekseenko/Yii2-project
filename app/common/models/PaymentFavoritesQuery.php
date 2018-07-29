<?php

namespace common\models;

use yii;

/**
 * This is the ActiveQuery class for [[PaymentFavorites]].
 *
 * @see PaymentFavorites
 */
class PaymentFavoritesQuery extends \yii\db\ActiveQuery
{

	/**
	 * @param int|array $id можно передавать массив. Тогда будет условие in
	 * @return PaymentTransactionsQuery
	 */
	public function byId($id)
	{
		return $this->andWhere([PaymentFavorites::tableName() . '.id' => $id]);
	}

	/**
	 * @return PaymentTransactionsQuery
	 */
	public function currentUser()
	{
		return $this->andWhere([PaymentFavorites::tableName() . '.user_id' => Yii::$app->user->id]);
	}

	/**
	 * @inheritdoc
	 * @return PaymentFavorites[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return PaymentFavorites|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}