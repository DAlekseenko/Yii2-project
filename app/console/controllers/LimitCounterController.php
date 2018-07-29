<?php

namespace console\controllers;

use common\models\Limits;

class LimitCounterController extends AbstractCronTask
{
	public function handler(array $params)
	{
		Limits::calculateMonthLimits();
	}
}
