<?php

namespace api\controllers;

use yii;
use common\models\Locations;
use common\models\LocationsSearch;

class LocationsController extends AbstractController
{

	public function actionIndex()
	{
		$regions = Locations::find()->roots()->select(['id', 'name'])->orderBy('name')->asArray()->cache()->all();

		$cities = Locations::find()->select(['id', 'name', 'parent_id' => 'tree_id'])->where(['=', 'level', 1])->orderBy('id')->asArray()->cache()->all();
		return [
			'regions' => $regions,
			'cities' => $cities,
		];
	}

	public function actionSetLocation($location_id)
	{
		$location = Locations::findById($location_id);
		if (empty($location)) {
			throw new yii\web\NotFoundHttpException('Location not found');
		}
		$user = Yii::$app->user;
		if (isset($user, $user->identity)) {
			$user->identity->location_id = $location->id;
			$user->identity->save();
		}

		return [
			'id' => $location_id,
			'label' => empty($location->parent_id) ? $location->name : $location->parents(1)->one()->name . ', ' . $location->name,
		];
	}

	public function actionFind()
	{
		$location = LocationsSearch::searchCity(Yii::$app->request->get('city'));
		if (!$location) {
			$location = LocationsSearch::searchRegion(Yii::$app->request->get('region'));
		}

		if ($location) {
			$result = ['id' => $location['id']];
			if ($location['level'] == 0) {
				$result['region'] = $location->toArray(['id', 'name']);
			} else {
				$result['city'] = $location->toArray(['id', 'name']);
				$result['region'] = $location->parents()->select(['id', 'name'])->limit(1)->asArray()->one();
			}
			return $result;
		}

		throw new yii\web\NotFoundHttpException('Location not found');
	}
}