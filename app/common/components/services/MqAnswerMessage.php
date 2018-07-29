<?php

namespace common\components\services;

class MqAnswerMessage extends MqOutputMessageAbstract
{
	protected $connectionId;

	protected $requestId;

	public function __construct($connectionId, $answerTo, $requestId = null)
	{
		$this->connectionId = $connectionId;
		$this->answerTo = $answerTo;
		$this->requestId = $requestId;
	}

	public function getAsArray()
	{
		return [
			'request'       => array_merge(isset($this->requestId) ? ['request_id' => $this->requestId] : [], $this->content),
			'connection_id' => $this->connectionId,
			'answer_to'     => $this->answerTo,
		];
	}
}
