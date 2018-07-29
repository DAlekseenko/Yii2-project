<?php

namespace common\components\services;

abstract class MqOutputMessageAbstract
{
	protected $answerTo;

	protected $content = [];

	abstract public function getAsArray();

	public function setContent(array $content)
	{
		$this->content = $content;
	}

	public function getAnswerTo()
	{
		return $this->answerTo;
	}

	public function __toString()
	{
		return json_encode($this->getAsArray(), JSON_UNESCAPED_UNICODE);
	}
}

