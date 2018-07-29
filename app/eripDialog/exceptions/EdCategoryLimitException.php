<?php

namespace eripDialog\exceptions;

class EdCategoryLimitException extends EdLimitException
{
	protected $categoryName;

	/**
	 * @return mixed
	 */
	public function getCategoryName()
	{
		return $this->categoryName;
	}

	/**
	 * @param $categoryName
	 * @return $this
	 */
	public function setCategoryName($categoryName)
	{
		$this->categoryName = $categoryName;

		return $this;
	}
}