<?php
use common\helpers\Html;
/**
 * @var \common\models\Categories $category
 */
?>
<form id="searchForm" class="search" action="/search/page-search">
	<input class="search_input" placeholder="Поиск предприятий и услуг<?=!empty($category) ? ' в категории ' . htmlspecialchars($category['name']) : ''?>" type="text" name="value">
	<?= Html::mtsButton('Найти', ['class' => '-search'])?>
</form>