<?php

namespace common\components\behaviors;

use yii;
use yii\base\Behavior;
use yii\base\UnknownMethodException;
use common\models\layouts\LayoutFactory;

abstract class RenderLayout extends Behavior
{
	protected $layoutFactory = null;

	/**
	 * @return LayoutFactory
	 */
	public function getLayoutFactory()
	{
		if ($this->layoutFactory === null) {
			$this->setLayoutFactory();
		}
		return $this->layoutFactory;
	}

	protected function setLayoutFactory()
	{

	}

	public function renderLayout($name)
	{
		$layout = $this->getLayoutFactory()->getLayout($name)->prepare();

		return $layout->isDisabled() ? '' : $this->owner->renderPartial($layout->getTemplate(), $layout->exportProperties());
	}

	public function __call($name, $params)
	{
		try {
			return parent::__call($name, $params);
		} catch (UnknownMethodException $e) {
			if (substr($name, 0, 3) == 'get' && substr($name, -6) == 'Layout') {
				return $this->getLayoutFactory()->getLayout(lcfirst(substr($name, 3, -6)));
			} elseif (substr($name, 0, 6) == 'render') {
				return $this->renderLayout(lcfirst(substr($name, 6)));
			}
			throw $e;
		}
	}

	function hasMethod($name)
	{
		if (substr($name, 0, 3) == 'get' && substr($name, -6) == 'Layout') {
			return true;
		}
		if (substr($name, 0, 6) == 'render') {
			return true;
		}
		return parent::hasMethod($name);
	}
}
