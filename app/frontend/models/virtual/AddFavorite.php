<?php

namespace frontend\models\virtual;

use common\models\PaymentFavorites;
use common\models\PaymentTransactions;
use Yii;
use yii\base\Model;

class AddFavorite extends Model
{
	public $name;

	public $id;

	public function rules()
	{
		return [
			['name', 'required'],
			['name', 'string', 'max' => 32],
		];
	}

	public function attributeLabels()
	{
		return [
			'name' => 'название',
		];
	}

	public function add(PaymentTransactions $paymentHistoryItem, $userId)
	{
		$favorite = new PaymentFavorites();
		$favorite->name = $this->name;
		$favorite->user_id = $userId;
		$favorite->service_id = $paymentHistoryItem->service_id;
		$favorite->fields = $paymentHistoryItem->fields;
		$favorite->transaction_id = $paymentHistoryItem->id;

		return $favorite->insert() ? $favorite->id : 0;
	}
}
