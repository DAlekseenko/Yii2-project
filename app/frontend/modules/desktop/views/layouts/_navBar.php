<?php
/* @var $this \yii\web\View */
$curActionid = Yii::$app->controller->action->id;
$items = [
	'about' => [
		'url' => ['/help/about'],
		'label' => 'Описание сервиса'
	],
//	'faq' => [
//		'url' => ['/help/faq'],
//		'label' => 'FAQ сервиса'
//	],
//	'how-to-connect' => [
//		'url' => ['/help/how-to-connect'],
//		'label' => 'Как подключиться'
//	],
//	'tarif' => [
//		'url' => ['/help/tarif'],
//		'label' => 'Условия и тарифы'
//	],
];
?>
<nav class="top-part_nav">
	<?php foreach($items as $actionId => $item):?>
		<?= \yii\helpers\Html::a($item['label'], $item['url'], ['class' => 'top-part_nav_link' . ($actionId === $curActionid ? ' -act' : '')])?>
	<?php endforeach;?>
</nav>
