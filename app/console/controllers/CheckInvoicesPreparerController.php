<?php

namespace console\controllers;

use common\models\Invoices;
use console\models\InvoicesUpdateState;
use common\components\services\MailService;
use Yii;

/**
 * Class CheckInvoicesPreparerController
 * @package console\controllers
 */
class CheckInvoicesPreparerController extends AbstractCronTask
{
    /**
     * @param string[] $params
     * @return void
     * @throws \Exception
     */
    public function handler(array $params)
	{
        $time = new \DateTime('now');
		$reportFile = Yii::getAlias('@runtime') . '/data/csv_invoices_state_' . $time->modify('-1 day')->format('Y-m-d') . '.csv';
        InvoicesUpdateState::saveInvoiceStateInCSV($reportFile);

        InvoicesUpdateState::deleteAll();

		/** @var Invoices $invoice */
		foreach (Invoices::getActiveWithUserDataIdQuery()->each() as $invoice) {
			if (isset($invoice->user_data_id)) {
				InvoicesUpdateState::saveInvoiceInState($invoice);
			}
			$invoice->delete();
		}
		foreach (InvoicesUpdateState::getUnaccountedUsersDataQuery()->each() as $userData) {
			InvoicesUpdateState::saveUserDataInState($userData);
		}
        MailService::sendStateReportCSVOnCheckInvoicesPrepare($reportFile);
	}
}
