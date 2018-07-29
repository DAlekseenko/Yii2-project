<?php

namespace console\controllers;

use console\models\CheckInvoices;
use console\models\IOMultithreadFormatter;
use Yii;
use yii\console\Controller;

/** @noinspection LongInheritanceChainInspection
 * Контроллер, который вызывается в виде подпроцесса выставления начислений.
 * Class CheckInvoicesController
 * @package console\controllers
 */
class CheckInvoicesController extends Controller
{
	/**
	 * @param $idsString - список идентификаторов состояния. Передаются в формате id1_id2_.._idN
	 * @throws \Exception
	 * @return void
	 */
	public function actionIndex($idsString)
	{
		Yii::beginProfile("Check invoices start process. (ids list: $idsString)");
		$start = microtime(true);
		try {
			$ids = explode('_', $idsString);
			if (empty($ids)) {
				throw new \Exception('Empty input ids list');
			}
			$checker = new CheckInvoices();
			$result = $checker->run($ids);

			IOMultithreadFormatter::writeProcessOutput($result);

			Yii::endProfile(
				"Check invoices process end. (ids list: $idsString) " .
				'Time: ' . number_format(microtime(true) - $start, 3, ',', ' ') . 'sec. ' .
				'Count of successfully inserted: ' . count($result)
			);
		} catch (\Exception $e) {
			Yii::endProfile("Check invoices process error: {$e->getMessage()}; ids list: $idsString)");
		}
	}
}
