<?php
/**
 * @var common\models\Categories[] $categories
 * @var common\models\Categories $category
 */
$letters = [];
$countChildren = count($categories);
$level = !empty($category) ? $category['level'] + 1 : 0;
?>
<div class="categories-list">
	<div class="categories-list-block">
		<?php foreach ($categories as $i => $child): ?>

		<?php //-----Когда прошло 3 буквы, то рисуем эти 3 буквы и начинаем новый блок?>
		<?php $letters[mb_strtoupper(mb_substr($child['name'], 0, 1, 'utf-8'), 'utf-8')] = true;?>
		<?php if (count($letters) > 3): ?>
		<?= $this->render('//partial/categories/categoryLetters', ['letters' => array_slice(array_keys($letters), 0, 3)])?>
		<?php //разделяем блок ?>
	</div><div class="categories-list-block">
		<?php $letters = array_slice($letters, 3, null, true); ?>
		<?php endif; ?>

		<?php $url = \yii\helpers\Url::to(['/categories', 'id' => $child['id']]) ?>
		<section class="categories-list-block_item">
			<div class="categories-list-block_link">
				<a class="--category-item" data-id="<?=$child['id']?>" href="<?= $url ?>"><?= $child['name'] ?> (<?=$child->services_count?>)</a>
			</div>
			<div class="categories-list-block_category-path">
				<?=implode(' / ', array_slice($child->getCategoryNamePath(), $level))?>
			</div>
		</section>

		<?php if ($countChildren == $i + 1): ?>
			<?= $this->render('//partial/categories/categoryLetters', ['letters' => array_keys($letters)])?>
		<?php endif; ?>
		<?php endforeach; ?>
	</div>
</div>