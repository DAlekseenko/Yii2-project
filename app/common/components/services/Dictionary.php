<?php

namespace common\components\services;

class Dictionary
{
	public static function __callStatic($name, $arguments)
	{
		$params = \yii::$app->params;
		if (isset($params['dictionary'][$name])) {
			$text = $params['dictionary'][$name];

			return isset($arguments[0]) ? self::replace($text, $arguments[0]) : $text;
		}
		return null;
	}

	protected static function replace($text, $params)
	{
		foreach ($params as $key => $value) {
			$text = str_replace("{{$key}}", $value, $text);
		}

		return $text;
	}
}
