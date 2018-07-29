<?php

namespace eripDialog;

use yii\log\FileTarget;

class EdFileTarget extends FileTarget
{
	public $logVars = [];

	public $fileMode = 0666;

	public function formatMessage($message)
	{
		list($text, $level, $category, $timestamp) = $message;

		return date('Y-m-d H:i:s', $timestamp) . " $text\n";
	}
}
