<?php
/**
 * @var $this yii\web\View
 * @var $context \yii\web\Controller|\frontend\modules\desktop\components\behaviors\RenderLayout
 * @var $dataProvider \yii\data\ActiveDataProvider
 */
?>

<?php
$defaultSettings = [
	'itemView' => '//partial/payments/_favoritesItemView',
	'layout' => "{items}\n{pager}",
	'options' => ['class' => 'mts-list-view'],
	'itemOptions' => ['class' => 'mts-list-view_item'],
	'emptyText' => 'У Вас нет ни одного избранного платежа, соответствующего выбранным параметрам.',
	'emptyTextOptions' => ['class' => 'empty-text'],
	'pager' => [
		'class' => \common\components\widgets\LinkPager::className(),
		'options' => ['class' => 'pagination'],
	],
];
?>
<?= \yii\widgets\ListView::widget(array_merge($defaultSettings, $settings)); ?>