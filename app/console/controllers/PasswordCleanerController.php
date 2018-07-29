<?php

namespace console\controllers;


use common\models\UserPasswords;
use Yii;

/**
 * Class PasswordCleanerController
 * @package console\controllers
 */
class PasswordCleanerController extends AbstractCronTask
{
    /**
     * Максимально время работы задачи
     */
    const RUN_TIME = 30;

    /**
     * @param string[] $params
     * @return void
     */
    public function handler(array $params)
	{
		$rowsCount = UserPasswords::clearOldPasswords();
		Yii::info('Deleted ' . $rowsCount . ' old passwords');
	}
}
