<?php
/**
 * @var \common\models\Categories $category
 */
use yii\widgets\Breadcrumbs;
?>


<?php
$config = [
	'options' => ['class' => 'breadcrumb --breadcrumbs'],
	'homeLink' => [
		'label' => 'Все услуги',
		'url' => \Yii::$app->homeUrl,
		'class' => '--change-category',
	],
	'links' => $category->getBreadcrumbs(),
	'itemTemplate' => "<li class='breadcrumb_item'>{link}</li>\n",
	'activeItemTemplate' => "<li class='breadcrumb_item -act'><a>{link}</a></li>\n"
]; ?>
<?= Breadcrumbs::widget($config) ?>