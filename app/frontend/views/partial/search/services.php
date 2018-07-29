<?php
/**
 * @var common\models\Services[] $services
 * @var common\models\Categories $category
 */
$prevLocationPath = null;
$level = !empty($category) ? $category['level'] + 1 : 0;
$locationExist = null;
?>
<div class="services-list">
	<div class="services-list-block">
		<?php foreach($services as $service): ?>
		<? $locationExist = $locationExist === null ? !empty($service->location) : $locationExist; ?>
		<? $locationPath = $service['location'] ? implode(', ', $service->location->getLocationPath()) : ($locationExist ? 'Прочие услуги' : false); ?>
		<? if ($locationPath && $locationPath !== $prevLocationPath): ?>
			<? if ($prevLocationPath !== null): ?>
			<? //разделяем блок ?>
		</div><div class="services-list-block">
			<? endif; ?>
		<div class="services-list-block_location"><?=$locationPath?></div>
		<? endif; ?>

		<?php $url = \yii\helpers\Url::to(['/payments/pay', 'id' => $service['id']]) ?>
		<a href="<?=$url?>" class="services-list-block_item --service-item" data-id="<?=$service['id']?>">
			<? if ($service->hasImg()): ?>
			<?=\common\components\widgets\ServiceIcon::widget(['tagName' => 'span', 'item' => $service, 'options' => ['class' => 'services-list-block_logo']])?>
			<? endif; ?>
			<div class="services-list-block_link">
				<?=$service['name']?>
			</div>
			<div class="services-list-block_category-path">
				<?=implode(' / ', array_slice($service['category']->getCategoryNamePath(true), $level))?>
			</div>
		</a>
		<?php $prevLocationPath = $locationPath; endforeach;?>
	</div>
</div>