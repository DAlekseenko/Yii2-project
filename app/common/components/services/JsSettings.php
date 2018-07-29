<?php
namespace common\components\services;

class JsSettings {
	private static $settings = [];

	public static function set($key, $value) {
		self::$settings[$key] = $value;
	}

	public static function get()
	{
		return self::$settings;
	}
}