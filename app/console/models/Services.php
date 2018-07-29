<?php

namespace console\models;

use Yii;

class Services extends \common\models\Services
{
	public static function saveService(Categories $category, $item)
	{
		$service = new static();
		$service->setAttributesByItem($item);
		$service->setAttribute('category_id', $category->getPrimaryKey());
		if (!$service->save()) {
			throw new \Exception('Failed to save service: ' . serialize($service->getFirstErrors()));
		}
	}

	public function setAttributesByItem($item)
	{
		$item['id'] = $item['eripCode'];
		$item['location_id'] = !empty($item['geoId']) ? $item['geoId'] : 0;
		if (isset($item['comissionProviderPercent'])) {
			$item['provider_fee'] = $item['comissionProviderPercent'];
		}
		$this->setAttributes($item, false);
	}

	public static function isService($item)
	{
		return (bool) (int) $item['isService'];
	}
}