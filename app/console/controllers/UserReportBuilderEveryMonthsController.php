<?php

namespace console\controllers;

use api\models\admin\search\UsersSearch;
use common\components\services\builders\UsersReportBuilder;
use common\models\Reports;
use common\models\Users;

/**
 * Class UserReportBuilderForMonths
 * @package console\controllers
 */
class UserReportBuilderEveryMonthsController extends AbstractCronTask
{
	const STATUSES = [Users::USER_TYPE_USER, Users::USER_TYPE_SUBSCRIBER];

	public function handler(array $params)
    {
        if (!isset($params[0])) {
        	return false;
		}
    	$id = $params[0];
    	$searchModel = new UsersSearch();
        $searchModel->date_to = (new \DateTime())->format('Y-m-d');
        $searchModel->subscription = implode(',', self::STATUSES);
        $reportMaker = new UsersReportBuilder($searchModel);
        if (Users::findOne($id)) {
            $report = new Reports;
            $report->leader_id = $id;
            $report->file_name = 'Ежемесячный отчет (пользователи сервиса)';
            $report->path = $reportMaker->getFilePath();
            $report->report_maker = base64_encode(serialize($reportMaker));
            $report->save();
        }
        return true;
    }
}
