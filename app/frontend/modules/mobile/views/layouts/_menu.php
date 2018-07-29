<?php
/* @var $this \yii\web\View
 * @var $context \yii\web\Controller|\frontend\modules\mobile\components\behaviors\RenderLayout
 */
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
<div id="mainMenu" class="main-menu" style="display: none">
	<div class="main-menu_top">
		<div id="mainMenuClose" class="main-menu_close"></div>
		<?= $this->context->renderLocation(); ?>
	</div>
	<?= $this->context->renderUserMenu(); ?>
	<nav class="main-menu_nav">
		<?php foreach($items as $actionId => $item):?>
			<?= \yii\helpers\Html::a($item['label'], $item['url'], ['class' => 'main-menu_nav-link' . ($actionId === $curActionid ? ' -act' : '')])?>
		<?php endforeach;?>
	</nav>
</div>