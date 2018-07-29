<?php

namespace console\models;

use Yii;

class Locations extends \common\models\Locations
{

	public static function refreshLocations($items)
	{
		$count = 0;
		Yii::info("Refreshing locations...\n-");
		self::deleteAll();

		foreach ($items as $item) {
			$location = new self();
			$location->setAttributes($item, false);

			if (!$location->makeRoot()) {
				throw new \Exception('Failed to make location root: ' . serialize($location->getFirstErrors()));
			}

			if (!empty($item['children'])) {
				$count += self::saveLocationChildren($location, $item['children']);
			}
		}
		return count($items) + $count;
	}

	private static function saveLocationChildren(Locations $parent, $children)
	{
		$count = 0;
		foreach ($children as $item) {
			$location = new Locations();
			$location->setAttributes($item, false);

			if (!$location->appendTo($parent)) {
				throw new \Exception('Failed to append location child: ' . serialize($location->getFirstErrors()));
			}

			if (!empty($item['children'])) {
				$count += self::saveLocationChildren($location, $item['children']);
			}
		}
		return count($children) + $count;
	}
}