<?php

namespace common\components\services;


use api\models\admin\search\UsersSearch;
use common\models\Reports;
use common\models\Users;

class ReportBuilder
{

    private $header;


    const classList = [
        Reports::USER_REPORT => 'UsersSearch',
    ];

    public function __construct($type)
    {
        $class = self::classList[$type];

        if(class_exists($class)){
            if($class instanceof  Users){

            }
        }
    }

}