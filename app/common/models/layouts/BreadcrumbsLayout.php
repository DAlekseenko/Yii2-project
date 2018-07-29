<?php

namespace common\models\layouts;

class BreadcrumbsLayout extends AbstractLayout
{
	public function __construct($template = '', array $vars = [])
	{
		parent::__construct($template, $vars);
		$this->setBreadcrumbs([]);
	}

	public function setBreadcrumbs(array $breadcrumbs = [])
	{
		$this->setVar('breadcrumbs', $breadcrumbs);

		return $this;
	}

	public function appendBreadcrumb($name, $link = null)
	{
		$this->_vars['breadcrumbs'][] = empty($link) ? $name : ['label' => $name, 'url' => $link];

		return $this;
	}

	public function getBreadcrumbs()
	{
		return $this->getVar('breadcrumbs');
	}
}
