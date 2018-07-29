<?php

namespace common\models\layouts;

abstract class AbstractLayout
{
	protected $_template;

	protected $_vars;

	protected $_properties = [];

	protected $_disable = false;

	public function __construct($template = '', array $vars = [])
	{
		if (!empty($template)) {
			$this->_template = $template;
		}
		$this->_vars = $vars;
		$this->init();
	}

	public function init() {}

	/**
	 * @return string
	 */
	public function getTemplate()
	{
		return $this->_template;
	}

	/**
	 * @param  string $template
	 * @return $this
	 */
	public function setTemplate($template)
	{
		$this->_template = $template;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getVars()
	{
		return $this->_vars;
	}

	/**
	 * @param $vars
	 * @return $this
	 */
	public function setVars(array $vars)
	{
		$this->_vars = $vars;

		return $this;
	}

	/**
	 * @param $key
	 * @param $value
	 * @return $this
	 */
	public function setVar($key, $value)
	{
		$this->_vars[$key] = $value;

		return $this;
	}

	/**
	 * @param $key
	 * @return mixed
	 */
	public function getVar($key)
	{
		return $this->_vars[$key];
	}

	public function disable()
	{
		$this->_disable = true;

		return $this;
	}

	public function enable()
	{
		$this->_disable = false;

		return $this;
	}

	public function isDisabled()
	{
		return $this->_disable;
	}

	protected function makeProperties($prop)
	{
		$this->_properties = $prop;
	}

	public function prepare()
	{
		return $this;
	}

	public function exportProperties()
	{
		return array_merge(['layout' => $this], $this->_properties, $this->getVars());
	}
}