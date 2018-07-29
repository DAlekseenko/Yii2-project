<?php

namespace common\components\services\senders;

use common\components\services\senders\messageFormatter\MessageFormatterInterface;
use common\models\Users;

interface SenderInterface
{
	public function __construct(MessageFormatterInterface $messageFormatter);

	/**
	 * Принимает сообщение. Сендер может принять или не принять сообщение согласно своей логики.
	 *
	 * @param Users $user
	 * @param null|mixed $data
	 * @return bool
	 */
	public function applyMessage(Users $user, $data = null);

	public function send();

	public function clearData();
}
