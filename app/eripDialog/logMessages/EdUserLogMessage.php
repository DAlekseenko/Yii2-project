<?php

namespace eripDialog\logMessages;

use common\models\Users;
use yii;

class EdUserLogMessage implements IEdLogMessage
{
	const SOURCE = 'userDialog';

	protected $identifier;

	protected $request;

	protected $result;

	protected $userId = null;

	protected $phone = null;

	public function __construct($identifier, $request, $result, $userId = null)
	{
		$this->identifier = $identifier;
		$this->request = is_array($request) ? $request : @json_decode($request, 1);
		$this->result  = is_array($result) 	? $result : @json_decode($result, 1);

		// Если пользователь не задан, то пытаемся вытащить его из локатора
		if (empty($userId) && isset(yii::$app->user, yii::$app->user->identity)) {
			$this->userId = yii::$app->user->id;
			$this->phone = yii::$app->user->identity->phone;
		}
		if (isset($userId) && !empty($user = Users::findIdentity($userId))) {
			$this->userId = $user->user_id;
			$this->phone = $user->phone;
		}
	}

	public function __toString()
	{
		return json_encode(
			[
				'mode' => static::SOURCE,
				'user_id' => $this->userId,
				'phone' => $this->phone,
				'session' => $this->identifier,
				'to_erip' => $this->request,
				'from_erip' => $this->result
			],
		JSON_UNESCAPED_UNICODE);
	}
}
