<?php

namespace common\models\layouts;

use yii\base\Exception;

class LayoutFactory
{
	protected $_layouts = [];

	public function setLayout($name, $layout)
	{
		$this->_layouts[$name] = $layout;

		return $this;
	}

	/**
	 * @param $name
	 * @return AbstractLayout
	 * @throws Exception
	 */
	public function getLayout($name)
	{
		if (!isset($this->_layouts[$name])) {
			throw new Exception('Layout with name ' . $name . ' was not found');
		}
		$layout = $this->_layouts[$name];
		if (is_string($layout)) {
			$this->_layouts[$name] = new $layout();
		} elseif (is_callable($layout)) {
			$this->_layouts[$name] = $layout();
		}

		if ($this->_layouts[$name] instanceof AbstractLayout) {
			return $this->_layouts[$name];
		}
		throw new Exception('Layout with name ' . $name . ' must be an instance of AbstractLayout');
	}
}
