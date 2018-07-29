<?php

namespace eripDialog;

class EdLogger
{
	protected $mode;

	protected $outKey;

	protected $fields = [];

	public function __construct($mode, $outKey)
	{
		$this->mode = $mode;
		$this->outKey = $outKey;
	}

	public function clearFields()
	{
		$this->fields = [];
	}

	public function addField($name, $value)
	{
		$this->fields[$name] = $value;
	}

	public function writeLog()
	{
		$message = json_encode(array_merge(['mode' => $this->mode], $this->fields), JSON_UNESCAPED_UNICODE);

		\yii::info($message, $this->outKey);
	}
}
