<?php
namespace common\components\widgets;

class LinkPager extends \yii\widgets\LinkPager {
	public $nextPageLabel = 'Следующая&rarr;';
	public $prevPageLabel = '&larr;Предыдущая';
	public function init() {
		parent::init();
		$currentPage = $this->pagination->getPage();
		if ($currentPage <= 0) $this->prevPageLabel = false;
		if ($currentPage + 1 >= $this->pagination->getPageCount()) $this->nextPageLabel = false;
	}
}