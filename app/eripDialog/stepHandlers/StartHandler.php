<?php

namespace eripDialog\stepHandlers;

use console\models\Services;
use eripDialog\EdHelper as H;

class StartHandler extends AbstractEripCaller
{
	protected function getNextMode()
	{
		return $this->response->isSum() ? H::MODE_PAY : H::MODE_FIELDS;
	}

	public function prepareClientOutput()
	{
		$result = parent::prepareClientOutput();
		$response = $this->response;
		if ($response->hasErrors()) {
			return $result;
		}
		$this->request->getServiceCode();
		$service = Services::findById($this->request->getServiceCode(), true);

		foreach ($result[H::F_FIELDS] as &$item) {
			if (isset($item['editable']) && $item['editable'] == true) { // находим первое редактируемое поле
				if (isset($service->servicesInfo)) {
					if (!empty($service->servicesInfo->first_field_name)) {
						$item['description'] = $service->servicesInfo->first_field_name;
					}
					$item['mask'] = $service->servicesInfo->mask;
				}
				break;
			}
		}

		return $result;
	}
}
