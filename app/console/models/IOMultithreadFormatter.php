<?php

namespace console\models;

class IOMultithreadFormatter
{
	public static function writeProcessOutput(array $result)
	{
		foreach ($result as $item) {
			print implode('_', $item) . "\n";
		}
	}

	/**
	 * @param  resource $d
	 * @return array
	 */
	public static function readProcessOutput($d)
	{
		$result = [];
		if (!is_resource($d)) {
			return $result;
		}
		while (!feof($d)) {
			$line = trim(fgets($d, 1024));
			if (!empty($line)) {
				$result[] = explode('_', $line);
			}
		}
		return $result;
	}
}
