<?php
/**
 * @var common\models\Services[] $services
 */
$prevLocationPath = null;
$locationExist = null;
?>
<h2 class="services-list-header">Услуги</h2>
<div class="services-list">
	<div class="services-list-block">
		<? foreach($services as $service): ?>
			<? $locationExist = $locationExist === null ? !empty($service->location) : $locationExist; ?>
			<?php $locationPath = $service['location'] ? implode(', ', $service['location']->getLocationPath()) : ($locationExist ? 'Прочие услуги' : false); ?>

			<?php if ($locationPath && $locationPath !== $prevLocationPath): ?>
				<?php if ($prevLocationPath !== null): ?>
					<?php //разделяем блок ?>
					</div><div class="services-list-block">
				<?php endif; ?>
				<div class="services-list-block_location"><?=$locationPath?></div>
			<?php endif; ?>

			<?php $url = \yii\helpers\Url::to(['/payments/pay', 'id' => $service['id']]) ?>
			<a href="<?=$url?>" class="services-list-block_item">
				<? if ($service->hasImg()): ?>
					<?=\common\components\widgets\ServiceIcon::widget(['tagName' => 'span', 'item' => $service, 'options' => ['class' => 'services-list-block_logo']])?>
				<? endif; ?>
				<div class="services-list-block_link">
					<?=$service['name']?>
				</div>
				<div class="services-list-block_description">
					<?=$service['description']?>
				</div>
			</a>
		<?php $prevLocationPath = $locationPath; endforeach;?>
	</div>
</div>