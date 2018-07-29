<?php

namespace eripDialog\exceptions;

class EdStepException extends \Exception
{
	protected $step;

	public function __construct($message, $step)
	{
		$this->step = (int) $step;

		parent::__construct($message);
	}

	public function getStep()
	{
		return $this->step;
	}
}
