<?php
namespace frontend\models\virtual;

use common\models\Services;
use Yii;
use yii\helpers\Url;

class ServicesSearch extends \common\models\virtual\ServicesSearch
{

	protected function prepareServiceAutocomplete(Services $service)
	{
		$result = parent::prepareServiceAutocomplete($service);
		$result['isService'] = true;
		$result['url'] = '/payments/pay?id=' . $service['id'];

		$parents = $service['category']->getCategoryNamePath(true);
		if ($parents) {
			$result['categoryPath'] = implode(' / ', $parents);
		}

		if ($service['location']) {
			$parents = $service['location']->getLocationPath();
			if ($parents) {
				$result['locationPath'] = implode(' / ', $parents);
			}
		}
		return $result;
	}
}
