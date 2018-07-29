<?php

namespace common\components\widgets;

class RecommendationIcon extends Icon
{
	public $tagName = 'span';

	public function init() {}

	public function run()
	{
		return $this->renderViewFile($this->imgView, [
			'item' => $this->item,
			'tagName' => $this->tagName,
			'options' => $this->options,
			'imgOptions' => $this->imgOptions,
		]);
	}
}
