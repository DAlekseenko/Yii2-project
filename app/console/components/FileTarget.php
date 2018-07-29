<?php

namespace console\components;

use yii\log\FileTarget as BaseTarget;
use yii\log\Logger;
use yii\helpers\VarDumper;

class FileTarget extends BaseTarget
{
	public $logVars = [];

	public function formatMessage($message)
	{
		list($text, $level, $category, $timestamp) = $message;
		$level = Logger::getLevelName($level);
		if (!is_string($text)) {
			// exceptions may not be serializable if in the call stack somewhere is a Closure
			if ($text instanceof \Exception) {
				$text = $text->getMessage() . ' in ' . $text->getFile() . ' line: ' . $text->getLine();
			} else {
				$text = VarDumper::export($text);
			}
		}
		$prefix = $this->getMessagePrefix($message);
		return date('Y-m-d H:i:s', $timestamp) . " {$prefix}[$level][$category] $text";
	}
}
