<?php

namespace common\models\sql;

trait EntitySearchHelperTrait
{
	protected $servicePrefix = 'ss';

	public function getGlobalCategoryConstrains(array $locations)
	{
		$ss = $this->servicePrefix;
		$items = ["'glob'"];
		if (count($locations) == 2) {
			$items[] = "'reg$locations[1]'";
			$items[] = "'loc$locations[1]'";
		} elseif (count($locations) == 3) {
			$items[] = "'loc$locations[1]'";
			$items[] = "'loc$locations[2]'";
		}
		return ' ( ' . $ss . '.meta ?| array[ ' . implode(', ', $items) . ' ] ) ';
	}
}
