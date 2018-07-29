<?php
/* @var $this yii\web\View */
/* @var $roots common\models\Categories[] */
/* @var array $children*/
use yii\helpers\Url;
?>
<div class="categories">
	<?php foreach($roots as $root):?>
		<?php $url = Url::to(['/categories', 'id' => $root['id']]); ?>
		<section class="category">
			<div class="category_info">
				<header class="category_header"><a href="<?=$url?>"><?=$root['name']?> (<?=$root->services_count?>)</a></header>
				<div class="category_description"><?=$root['description_short']?></div>
			</div>
			<div class="payment-logos">
				<?php if (!empty($children[$root['id']])): ?>
					<div class="payment-logos_list">
						<?php foreach($children[$root['id']] as $child):?>
							<?php if ($child instanceof \common\models\Categories):?>
								<?=\common\components\widgets\CategoryIcon::widget(['item' => $child])?>
							<?php else: ?>
								<?=\common\components\widgets\ServiceIcon::widget(['item' => $child])?>
							<?php endif;?>
						<?php endforeach;?>
					</div>
				<?php endif; ?>
			</div>
		</section>
	<?php endforeach;?>
</div>