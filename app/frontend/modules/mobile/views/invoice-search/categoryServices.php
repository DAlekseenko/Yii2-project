<?php
/**
 * @var \frontend\models\InvoicesUsersData $model
 * @var \common\models\Categories $category
 * @var \common\models\Services $activeService
 * @var \common\models\Services[] $services
 * @var \common\models\Categories $globalCategory
 */
$breadcrumbsLayout = $this->context->getBreadcrumbsLayout();
$this->title = 'МТС Деньги - Добавить идентификатор';
$this->params['header'] = 'Добавить идентификатор';
$breadcrumbsLayout
	->appendBreadcrumb('Мои счета на оплату', '/invoices')
	->appendBreadcrumb('Добавить идентификатор', '/invoice-search/index');
?>
<div class="invoice-search -mobile-padding">
	<div class="invoice-search_header-small">
		<h2>
			<?= $category['name'] ?>
		</h2>
		<? if ($category->hasImg()): ?>
			<?= \common\components\widgets\CategoryIcon::widget(['item' => $category, 'tagName' => 'span']) ?>
		<? endif; ?>
	</div>

	<div class="invoice-search-services">
		<div class="invoice-search-services_form">
			<?= $this->render('/invoices/invoicesUsersDataCreate', ['service' => $activeService, 'model' => $model]) ?>
		</div>

		<h4 class="invoice-search-services_header">Выберите услугу</h4>
		<div class="invoice-search-services_content">
			<?php foreach($services as $service):?>
				<a class="invoice-search-services_item<?=$activeService['id'] == $service['id'] ? ' -act' : ''?> --service-item" data-id="<?=$service['id']?>">
					<div class="invoice-search-services_item-name"><?= $service['name'];?></div>

					<?php $locationPath = $service['location'] ? implode(', ', $service['location']->getLocationPath()) : false; ?>
					<?php if ($locationPath): ?>
						<h5 class="invoice-search-services_item-location"><?=$locationPath?></h5>
					<?php endif; ?>
				</a>
			<?php endforeach;?>
		</div>
	</div>
</div>