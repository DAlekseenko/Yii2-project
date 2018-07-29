<?php

namespace common\components\services\senders\messageFormatter;

use common\models\Users;

class AndroidPushMessage extends AbstractMessageFormatter
{
	public function prepareMessage(Users $user, $data, $senderName = null)
	{
		$this->sender = $senderName;
		try {
			return [$data[0]];
		} catch (\Exception $e) {
			return false;
		}
	}
}