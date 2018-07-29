<?php
/* @var yii\web\View $this */
/* @var $context \yii\web\Controller|\frontend\modules\desktop\components\behaviors\RenderLayout */
/* @var common\models\Categories $category */
/* @var common\models\Categories[] $closestChildren */
/* @var common\models\Services $service */
/* @var array $breadcrumbs */

$this->title = 'МТС Деньги - Все платежи';
$context = $this->context;

$context->getBreadcrumbsLayout()->setBreadcrumbs($category->getBreadcrumbs());
$context->getHeaderLayout()->setTemplate('_header')->setVars(['category' => $category]);
?>

<div class="-mobile-padding">
	<?= $this->render('/partial/searchForm', ['category' => $category]); ?>

	<main id="searchResult" class="-mt30">
		<?php if ($closestChildren || $category['services']): ?>
			<?php if ($closestChildren): ?>
				<?=$this->render('//partial/categories/categories', ['categories' => $closestChildren])?>
			<?php endif; ?>

			<?php if ($category['services']): ?>
				<?=$this->render('//partial/categories/services', ['services' => $category['services']])?>
			<?php endif; ?>
		<?php else: ?>
			<h3>В <span class="-bold"><?=implode(', ', \common\models\Locations::getCurrentLocation()->getLocationPath())?></span> услуг не найдено</h3>
		<?php endif; ?>
	</main>
</div>