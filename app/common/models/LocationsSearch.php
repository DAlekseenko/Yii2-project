<?php

namespace common\models;

use yii;

//используется при автоматическом определении города. Из яндекса мы получаем название города и после этого по этому названию пытаемся найти
//локацию у нас в базе
class LocationsSearch extends Locations
{

	public static function searchCity($name)
	{
		if ($name) {
			$location = Locations::find()->where(['ilike', 'name', $name])->orderBy('name')->limit(1)->one();
			if ($location) {
				if ($location['level'] > 1) {
					$location = $location->parents()->where(['level' => 1])->limit(1)->one();
				}
				return $location;
			}
		}
		return false;
	}

	public static function searchRegion($name)
	{
		if ($name) {
			$name = str_replace(['обл.', 'область'], '', mb_strtolower($name, 'UTF-8'));
			return Locations::find()->roots()->andWhere(['ilike', 'name', explode(' ', trim($name))[0]])->orderBy('name')->limit(1)->one();
		}
		return false;
	}
}