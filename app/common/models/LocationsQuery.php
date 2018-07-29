<?php

namespace common\models;

use common\components\behaviors\NestedSetsQueryBehavior;

/**
 * This is the ActiveQuery class for [[Cities]].
 *
 * @see Locations
 * @method LocationsQuery roots()
 * @method LocationsQuery leaves()
 */
class LocationsQuery extends \common\components\db\ActiveQuery
{

	public function behaviors()
	{
		return [
			'nestedSetsQueryBehavior' => [
				'class' => NestedSetsQueryBehavior::class
			]
		];
	}

	/**
	 * @inheritdoc
	 * @return Locations[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return Locations|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}