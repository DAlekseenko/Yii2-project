<?php

namespace api\controllers;

use common\models\Documents;

class HelpController extends AbstractController
{
    /**
     * @return bool|static
     */
    function actionServiceDescription()
    {
        return Documents::findByKey(Documents::KEY_FAQ);
    }

    /**
     * @return static
     */
    public function actionUserAgreement()
    {
        return Documents::findByKey(Documents::KEY_RULES);
    }


}