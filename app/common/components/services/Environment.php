<?php

namespace common\components\services;

class Environment implements \JsonSerializable
{
	const MODULE_ROBOT = 'robot';//Инициация действия происходит автоматически (крон, какой-либо скрипт)
	const MODULE_WEB   = 'web';  //... через веб
	const MODULE_USSD  = 'ussd'; //... чере ussd
	const MODULE_APP   = 'app';  //... через приложение

	protected $name = self::MODULE_ROBOT;

	protected $prop = [];

	/**
	 * @param $name
	 * @return $this
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param array $prop
	 * @return $this
	 */
	public function setProp(array $prop = [])
	{
		$this->prop = $prop;

		return $this;
	}

	public function getIpAddr()
	{
		return isset($this->prop['ip']) ? $this->prop['ip'] : null;
	}
	public function getProps()
	{
		return $this->prop;
	}

	public function jsonSerialize()
	{
		return [
			'name' => $this->name,
			'prop' => $this->prop
		];
	}
}
