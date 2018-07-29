<?php

namespace console\controllers;


use common\models\AssistTransactions;

/**
 * Class AssistCleanerController
 * @package console\controllers
 */
class AssistCleanerController extends AbstractCronTask
{
    public function handler(array $params)
    {
        AssistTransactions::deleteAll(['status' => 'New']);
    }
}



