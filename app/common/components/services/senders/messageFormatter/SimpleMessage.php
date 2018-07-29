<?php

namespace common\components\services\senders\messageFormatter;

use common\components\services\senders\ApplePushSender;
use common\components\services\senders\GooglePushSender;
use common\models\Users;

class SimpleMessage extends AbstractMessageFormatter
{

    public function prepareMessage(Users $user, $data, $senderName = null)
    {
        $this->sender = $senderName;
        try {
            return $this->message($data);
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function message($data)
    {
        switch ($this->sender) {
            case ApplePushSender::class:
                return [$data[0]['text']];
            case GooglePushSender::class:
                return [$data[0]];
            default:
                return [];
        }
    }

}