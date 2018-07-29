<?php
/**
 * @var common\models\Categories[] $categories 
 */
$letters = [];
$countChildren = count($categories)
?>
<div class="categories-list">
	<div class="categories-list-block">
		<? foreach ($categories as $i => $child):
		 	//-----Когда прошло 3 буквы, то рисуем эти 3 буквы и начинаем новый блок
			$letters[mb_strtoupper(mb_substr($child->name, 0, 1, 'utf-8'), 'utf-8')] = true;
			$url = \yii\helpers\Url::to(['/categories', 'id' => $child->id]);
			if (count($letters) > 3): ?>
				<?= $this->render('//partial/categories/categoryLetters', ['letters' => array_slice(array_keys($letters), 0, 3)])?>
				</div><div class="categories-list-block">
				<? $letters = array_slice($letters, 3, null, true); ?>
			<? endif; ?>
			<section class="categories-list-block_item">
				<a href="<?= $url ?>"><?= $child->name ?> (<?= $child->services_count ?>)</a>
			</section>
			<? if ($countChildren == $i + 1): ?>
				<?= $this->render('//partial/categories/categoryLetters', ['letters' => array_keys($letters)])?>
			<? endif; ?>
		<? endforeach; ?>
	</div>
</div>