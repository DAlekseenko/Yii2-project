<?php

namespace console\controllers;


use common\models\AccessDeny;

/**
 * Class AccessDenyCleanerController
 * @package console\controllers
 */
class AccessDenyCleanerController extends AbstractCronTask
{
    /**
     * Максимальное время работы задачи
     */
    const RUN_TIME = 30;

    /**
     * @param string[] $params
     * @return void
     */
    public function handler(array $params)
	{
        AccessDeny::deleteAll('is_temporary AND action=:action', ['action' => $params[0]]);
	}
}
