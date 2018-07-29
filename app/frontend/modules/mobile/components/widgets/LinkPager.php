<?php
namespace frontend\modules\mobile\components\widgets;

class LinkPager extends \common\components\widgets\LinkPager {
	public $maxButtonCount = 9;
	public $nextPageLabel = '&rarr;';
	public $prevPageLabel = '&larr;';
	public $options = ['class' => 'pagination -mobile-padding'];
}