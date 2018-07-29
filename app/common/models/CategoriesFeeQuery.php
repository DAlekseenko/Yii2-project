<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[Categories]].
 *
 * @see CategoriesFee
 */
class CategoriesFeeQuery extends \common\components\db\ActiveQuery
{

	public function byCategoryId($categoryId) {
		return $this->andWhere(['category_id' => $categoryId]);
	}

	/**
	 * @return $this
	 */
	public function active()
	{
		$now = date('Y-m-d H:i:s');
		return $this->andWhere(['and', ['>', 'date_end', $now], ['<', 'date_start', $now]]);
	}

	/**
	 * @inheritdoc
	 * @return array|Categories[]
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return Categories|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}