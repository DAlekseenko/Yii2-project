<?php
namespace api\controllers;

use common\components\services\assist\AssistHelper;
use common\models\AssistTransactions;
use yii;

class SiteController extends AbstractController
{

	public function beforeAction($action)
	{
		if ($action->id == 'assist-callback') {
			$this->enableCsrfValidation = false;
		}

		return parent::beforeAction($action);
	}

	public function actionAssistCallback()
	{
		\yii::info('Assist notification request');
		$error = AssistHelper::checkData($_POST);

		if (empty($error)) {
			/** @var AssistTransactions $assistTransaction */
			$assistTransaction = AssistTransactions::findOne(['order_number' => $_POST['ordernumber']]);
			if (empty($assistTransaction)) {
				$error = AssistHelper::ERROR_NO_TRANSACTION;
			} else {
				$assistTransaction->status = $_POST['orderstate'];
				$assistTransaction->assist_data = $_POST;
				$error = $assistTransaction->save() ? 0 : AssistHelper::ERROR_INTERNAL;
			}
		}
		\yii::info('Assist notification complete (error: ' . $error . ')');

		echo empty($error)
			? '<?xml version="1.0" encoding="UTF-8"?><pushpaymentresult firstcode="0" secondcode="0"><order><billnumber>' . $_POST['billnumber']. '</billnumber><packetdate>' . $_POST['packetdate'] . '</packetdate></order></pushpaymentresult>'
			: '<?xml version="1.0" encoding="UTF-8"?><pushpaymentresult firstcode="' . $error . '" secondcode="0"></pushpaymentresult>';
		exit;
	}
}