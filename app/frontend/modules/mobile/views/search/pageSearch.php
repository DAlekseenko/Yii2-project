<?php
/* @var yii\web\View $this */
/* @var common\models\Categories[] $categories */
/* @var common\models\Services[] $services */
/* @var common\models\Categories $category */
/* @var array $breadcrumbs */
?>
<?php if ($categories): ?>
	<?=$this->render('//partial/search/categories', ['categories' => $categories, 'category' => $category])?>
<?php endif; ?>

<?php if ($services): ?>
	<h2 class="services-list-header">Услуги</h2>
	<?=$this->render('//partial/search/services', ['services' => $services, 'category' => $category])?>
<?php endif; ?>