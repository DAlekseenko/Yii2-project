<?php
/**
 * @var $breadcrumbs array
 */
use yii\widgets\Breadcrumbs;
$config = [
	'homeLink' => [
		'label' => 'Главная',
		'url' => \Yii::$app->homeUrl
	],
	'options' => ['class' => 'breadcrumb -mobile-padding'],
	'links' => $breadcrumbs,
	'itemTemplate' => "<li class='breadcrumb_item'>{link}</li>\n",
	'activeItemTemplate' => "<li class='breadcrumb_item -act'><a>{link}</a></li>\n"
]; ?>

<?= Breadcrumbs::widget($config) ?>