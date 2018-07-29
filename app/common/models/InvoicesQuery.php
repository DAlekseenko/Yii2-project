<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[Invoices]].
 *
 * @see Invoices
 */
class InvoicesQuery extends \common\components\db\ActiveQuery
{

	/**
	 * @return InvoicesQuery
	 */
	public function withService()
	{
		return $this->with(['service.servicesInfo', 'service.category.categoriesInfo']);
	}


	/**
	 * @param int|array $id можно передавать массив. Тогда будет условие in
	 * @return InvoicesQuery
	 */
	public function byId($id)
	{
		return $this->andWhere([Invoices::tableName() . '.id' => $id]);
	}

	/**
	 * @return InvoicesQuery
	 */
	public function currentUser()
	{
		return $this->whereUserId(\Yii::$app->user->id);
	}

	public function active($serviceId, $key)
	{
		$t = Invoices::tableName();
		return $this->andWhere("$t.payment_date IS NULL")->andWhere(["$t.service_id" => $serviceId, "$t.fields_key" => $key]);
	}

	public function whereUserId($userId)
	{
		$t = Invoices::tableName();
		return $this->andWhere(["$t.user_id" => $userId]);
	}

	/**
	 * @inheritdoc
	 * @return Invoices[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return Invoices|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}