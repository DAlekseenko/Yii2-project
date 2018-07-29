<?php

namespace common\components\db;


class DbSession extends \yii\web\DbSession
{
	/**
	 * Переопределяем метод подготовки данных для записи сессии в DB.
	 * Вытаскиваем идентификатор пользователя, если он есть, чтобы в дальнеёшем удалять все сессии при смене контракта пользователя.
	 *
	 * @param string $id
	 * @param string $data
	 * @return array
	 * @throws \Exception
	 */
	protected function composeFields($id, $data)
	{
		$fields = parent::composeFields($id, $data);

		$data = $this->unserialize($fields['data']);
		$userId = isset($data['__id']) ? $data['__id'] : null;

		return array_merge($fields, ['user_id' => $userId]);
	}

	private function unserialize($session_data) {
		$method = ini_get("session.serialize_handler");
		switch ($method) {
			case "php":
				return $this->unserialize_php($session_data);
				break;
			case "php_binary":
				return $this->unserialize_phpbinary($session_data);
				break;
			default:
				throw new \Exception("Unsupported session.serialize_handler: " . $method . ". Supported: php, php_binary");
		}
	}

	private static function unserialize_php($session_data) {
		$return_data = array();
		$offset = 0;
		while ($offset < strlen($session_data)) {
			if (!strstr(substr($session_data, $offset), "|")) {
				throw new \Exception("invalid data, remaining: " . substr($session_data, $offset));
			}
			$pos = strpos($session_data, "|", $offset);
			$num = $pos - $offset;
			$varname = substr($session_data, $offset, $num);
			$offset += $num + 1;
			$data = unserialize(substr($session_data, $offset));
			$return_data[$varname] = $data;
			$offset += strlen(serialize($data));
		}
		return $return_data;
	}

	private static function unserialize_phpbinary($session_data) {
		$return_data = array();
		$offset = 0;
		while ($offset < strlen($session_data)) {
			$num = ord($session_data[$offset]);
			$offset += 1;
			$varname = substr($session_data, $offset, $num);
			$offset += $num;
			$data = unserialize(substr($session_data, $offset));
			$return_data[$varname] = $data;
			$offset += strlen(serialize($data));
		}
		return $return_data;
	}
}
