<?php

namespace common\components\services\senders\messageFormatter;

use common\models\Users;

interface MessageFormatterInterface
{
	/**
	 * MessageFormatterInterface constructor.
	 * @param  string $type 			тип сообщения
	 * @param  bool   $canBeMultiple    флаг, указывающий - может ли сообщение быть составное
	 */
	public function __construct($type, $canBeMultiple);

	/**
	 * @param  Users  $user
	 * @param  mixed  $data
	 * @param  string $senderName
	 * @return mixed|false
	 */
	public function prepareMessage(Users $user, $data, $senderName = null);
}
