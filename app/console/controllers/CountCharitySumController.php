<?php

namespace console\controllers;

use common\models\Ussd;

class CountCharitySumController extends AbstractCronTask
{
	/**
	 * @param string[] $params
	 * @return void
	 * @throws \Exception
	 */
	public function handler(array $params)
	{
		Ussd::updateTotalSum();
	}
}
