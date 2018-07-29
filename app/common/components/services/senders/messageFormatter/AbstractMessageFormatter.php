<?php

namespace common\components\services\senders\messageFormatter;

abstract class AbstractMessageFormatter implements MessageFormatterInterface
{
	protected $type;

	protected $canBeMultiple;

    protected $sender;

	public function __construct($type, $canBeMultiple)
	{
		$this->type = $type;
		$this->canBeMultiple = $canBeMultiple;
	}
}