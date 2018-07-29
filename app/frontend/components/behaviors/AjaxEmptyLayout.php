<?php
namespace frontend\components\behaviors;

use yii;
use yii\base\Behavior;

class AjaxEmptyLayout extends Behavior
{

	public function events()
	{
		return [yii\web\Controller::EVENT_BEFORE_ACTION => 'beforeAction'];
	}

	public function beforeAction($event){
		if (Yii::$app->request->isAjax) {
			//если идет запрос к контроллеру и он isAjax, то отдаем лишь только контент(то, что вернул action)
			$event->action->controller->layout = 'empty';
		}
	}
}