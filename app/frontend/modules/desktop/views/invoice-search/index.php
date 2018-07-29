<?php
/**
 * @var \common\models\Categories $category
 * @var \common\models\Categories[] $categories
 * @var \common\models\Services[] $services
 * @var array $globalCategories
 */
use common\helpers\Html;
?>
<div class="invoice-search">
	<h1 class="invoice-search_header"><?=!empty($category) ? $category['name'] : 'Добавить'?></h1>

	<?php if (!empty($category)): ?>
		<?=$this->render('_breadcrumbs', ['category' => $category])?>
	<?php endif; ?>

	<h2 class="invoice-search_header-small">Выберите поставщика услуг</h2>

	<?php if (!empty($topItems)): ?>
		<?=$this->render('_topItems', ['topItems' => $topItems])?>
	<?php endif; ?>

	<div class="invoice-search_form">
		<form id="invoiceSearchForm">
			<div class="search">
				<?php if (!empty($category)): ?>
					<input type="hidden" name="categoryId" value="<?=$category['id']?>">
				<?php endif; ?>
				<input class="search_input --input" placeholder="Поиск предприятий и услуг" type="text" name="value" value="<?=isset($value) ? htmlspecialchars($value) : ''?>">
				<?= Html::mtsButton('Найти', ['class' => '-search'])?>
			</div>
			<div class="search-hint --hint">
				<?=!empty($hint) ? $hint : ''?>
			</div>
		</form>
	</div>

	<div id="invoiceSearchContent" class="invoiceSearchContent">
		<?php if (!empty($categories)): ?>
			<?=$this->render('//partial/search/categories', ['categories' => $categories, 'category' => !empty($category) ? $category : null])?>
		<?php endif; ?>

		<?php if (!empty($services)): ?>
			<h2 class="services-list-header">Услуги</h2>
			<?=$this->render('//partial/search/services', ['services' => $services, 'category' => !empty($category) ? $category : null])?>
		<?php endif; ?>
	</div>
</div>