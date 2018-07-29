<?php

namespace eripDialog\exceptions;

class EdLimitException extends \Exception
{
	protected $availableSum = null;

	public function setAvailableSum($sum)
	{
		$this->availableSum = $sum;

		return $this;
	}

	public function getAvailableSum()
	{
		return $this->availableSum;
	}
}
