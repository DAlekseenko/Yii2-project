<?php

namespace common\components\services\senders;

use common\models\UserDevices;
use common\models\Users;

/**
 * Class SenderGroupGate
 * @package common\components\services\senders
 */

class SenderGroupGate
{
    /**
     * @param Users $user
     * @param $device
     * @return bool
     */
    public static function newInvoicePushNotification(Users $user, $device)
    {
        return UserDevices::hasPush($user->user_id);
    }

    /**
     * @param Users $user
     * @param $device
     * @return bool
     */
    public static function simplePushMessage(Users $user, $device)
    {
        return UserDevices::hasPushByDevices($user->user_id, $device);
    }

}
