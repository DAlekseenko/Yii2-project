<?php

namespace common\components\services;

class MqCronSmsMessage extends MqOutputMessageAbstract
{
	public function getAsArray()
	{
		return ['answer_to' => $this->getAnswerTo(), 'transactionList' => $this->content];
	}

	public function __construct($queue, array $list)
	{
		$this->answerTo = $queue;
		$this->setContent($list);
	}
}
