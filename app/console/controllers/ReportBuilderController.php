<?php

namespace console\controllers;

use common\models\Reports;

/**
 * Class ReportBuilderController
 * @package console\controllers
 */
class ReportBuilderController extends AbstractCronTask
{
	const RUN_TIME = 7200; // секунд

	public function handler(array $params)
    {
		$reportModel = Reports::find()->where(['status' => Reports::REPORT_REGISTER])->orderBy('date_create')->limit(1)->one();
		if (!empty($reportModel)) {
			$this->buildReport($reportModel);
		}
    }

	/**
	 * @param $reportModel
	 * @return bool
	 */
    public function buildReport($reportModel)
    {
        $reportModel->status = Reports::REPORT_PERFORMED;
        $reportModel->update();
        $date = new \DateTime();
        try {
            $reportMaker = unserialize(base64_decode($reportModel->report_maker));
            if ($reportMaker->getFile()) {
                $reportModel->status = Reports::REPORT_SUCCESS;
                $reportModel->date_concluding = $date->format('Y-m-d H:i:s');
                $reportModel->update();
                return true;
            }
        } catch (\Exception  $e) {
            \yii::info('FILE LOAD WITH ERROR: ' . $e->getMessage());
            $reportModel->status = Reports::REPORT_FAILURE;
            $reportModel->date_concluding = $date->format('Y-m-d H:i:s');
            $reportModel->update();
            return false;
        }
        return false;
    }
}
