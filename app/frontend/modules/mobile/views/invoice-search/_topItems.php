<?php
/**
 * @var \common\models\Categories[] $topItems
 */
?>
<div class="global-categories">
	<?php foreach ($topItems as $childrenItem): ?>
		<?php if ($childrenItem instanceof \common\models\Categories): ?>
			<?php $url = \yii\helpers\Url::to(['/categories', 'id' => $childrenItem->id]) ?>
			<a href="<?=$url?>" class="global-categories_item --category-item" data-id="<?=$childrenItem->id?>">
				<? if ($childrenItem->hasImg()): ?>
					<?=\common\components\widgets\CategoryIcon::widget(['tagName' => 'span', 'item' => $childrenItem, 'options' => ['class' => 'global-categories_logo']])?>
				<? endif; ?>
				<?=$childrenItem->name?>
			</a>
		<?php else: ?>
			<?php $url = \yii\helpers\Url::to(['/payments/pay', 'id' => $childrenItem->id]) ?>
			<a href="<?=$url?>" class="global-categories_item --service-item" data-id="<?=$childrenItem->id?>">
				<? if ($childrenItem->hasImg()): ?>
					<?=\common\components\widgets\ServiceIcon::widget(['tagName' => 'span', 'item' => $childrenItem, 'options' => ['class' => 'global-categories_logo']])?>
				<? endif; ?>
				<?=$childrenItem->name?>
			</a>
		<?php endif; ?>
	<?php endforeach; ?>
</div>