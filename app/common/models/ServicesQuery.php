<?php

namespace common\models;

use frontend\models\InvoicesIgnoreDefault;

/**
 * This is the ActiveQuery class for [[Services]].
 *
 * @see Services
 */
class ServicesQuery extends \common\components\db\ActiveQuery
{

	/**
	 * @param int|array $id можно передавать массив. Тогда будет условие in
	 * @return $this
	 */
	public function byId($id)
	{
		return $this->andWhere([Services::tableName() . '.id' => $id]);
	}

	/**
	 * @param $value
	 * @return $this
	 */
	public function search($value)
	{
		$tableSi = ServicesInfo::tableName();
		$this->leftJoin($tableSi, Services::tableName() . '.id = ' . $tableSi . '.service_id');
		$condition = [
			'or',
			['ilike', Services::tableName() . '.name', $value],
			['ilike', $tableSi . '.name', $value],
		];
		return $this->andWhere($condition);
	}

	/**
	 * @param int|array $id можно передавать массив. Тогда будет условие not in
	 * @return $this
	 */
	public function ignoreId($id)
	{
		return $this->andWhere(['not in', Services::tableName() . '.id', (array)$id]);
	}

	public function userIgnoreDefault()
	{
		$ignoreTable = InvoicesIgnoreDefault::tableName();
		$on = ['and', Services::tableName() . '.id = ' . $ignoreTable . '.service_id', [$ignoreTable . '.user_id' => \Yii::$app->user->id]];
		return $this->leftJoin($ignoreTable, $on)->andWhere($ignoreTable . '.user_id is null');
	}

	public function isGlobal()
	{
		return $this->andWhere([ServicesInfo::tableName() . '.is_global' => true]);
	}

	public function currentLocation()
	{
		return $this->andWhere(['in', Services::tableName() . '.location_id', Locations::getCurrentLocationTreeIds()]);
	}

	/**
	 * @param string $field show_main | show_top
	 * @return $this
	 */
	public function byShowField($field)
	{
		$field = ServicesInfo::tableName() . '.' . $field;
		return $this->andWhere(['or', [$field => true], $field . ' is null'])->orderBy($field);
	}

	/**
	 * @inheritdoc
	 * @return array|Services[]
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return Services|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}