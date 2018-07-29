<?php

namespace console\controllers;

use Yii;
use console\models\CheckInvoicesMultithreadManager;
use console\models\CheckInvoicesSmsQueue;
use PbrLibBelCommon\Multiprocessing\JobsManager;
use console\models\InvoicesUpdateState;

/**
 * Class CheckInvoicesMultithreadController
 * @package console\controllers
 */
class CheckInvoicesMultithreadController extends AbstractCronTask
{
	const ITEMS_PER_PROCESS = 50; // обновляем по 50 начислений
	const THREAD_COUNT = 4;		  // в 4 потока.

    /**
     * @param string[] $params
     * @return void
     * @throws \Exception
     */
    public function handler(array $params)
	{
		$start = microtime(true);
        Yii::beginProfile('Check invoices multithread.');
		try {
			$smsQueueHandler = new CheckInvoicesSmsQueue(Yii::$app->params['invoiceCronSmsQueue'], Yii::$app->amqp);
			$jobsManager = new JobsManager(self::THREAD_COUNT);

			$manager = new CheckInvoicesMultithreadManager($smsQueueHandler, $jobsManager);

			list($existInvoicesMap, $newInvoicesMap) = $manager->createUpdateMap(InvoicesUpdateState::find()->each(), self::ITEMS_PER_PROCESS);

			$manager->processMap($existInvoicesMap);
			// нам выжно, чтобы запросы на отсылку sms формировались толко для новых начислений.
			$result = $manager->processMap($newInvoicesMap);
			$manager->appendSmsData($result);

			$smsQueueHandler->putToQueue();

            Yii::endProfile(
				'Check invoices multithread end.' .
				'Time: ' . number_format(microtime(true) - $start, 3, ',', ' ') . 'sec. '
			);
		} catch (\Exception $e) {
            Yii::endProfile("Check invoices multithread error: {$e->getMessage()}");
			throw $e;
		}
	}
}
