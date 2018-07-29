<?php

namespace common\components\services;

class MqJobMessage extends MqOutputMessageAbstract
{
	public function __construct($queue, $method, array $args = [])
	{
		$this->answerTo = $queue;
		$this->setContent([
			'method' => $method,
			'args'	 =>	$args
		]);
	}

	public function getAsArray()
	{
		return array_merge(['answer_to' => $this->getAnswerTo()], $this->content);
	}
}
