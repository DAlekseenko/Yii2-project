<?php
/**
 * @var $this yii\web\View
 * @var $form common\components\widgets\ActiveForm
 * @var \frontend\models\InvoicesUsersData $model
 * @var \common\models\Services $service
 * @var \common\models\Categories $category
 * @var \common\models\Categories $globalCategory
 */
use common\helpers\Html;
$breadcrumbs = $this->context->getBreadcrumbsLayout();
$this->title = 'МТС Деньги - Изменить идентификатор';
$this->params['header'] = $globalCategory['name'];
$breadcrumbs->appendBreadcrumb('Мои счета на оплату', '/invoices')->appendBreadcrumb($globalCategory['name']);
?>
<div class="invoice-search -mobile-padding">
	<div class="invoice-search_header-small">
		<h2>
			<?= $category->name ?>
		</h2>
		<? if ($category->hasImg()): ?>
			<?= \common\components\widgets\CategoryIcon::widget(['item' => $category, 'tagName' => 'span']) ?>
		<? endif; ?>
	</div>

	<div id="createUserInvoiceContainer" class="create-user-invoice">
		<h2 class="create-user-invoice_header"><?= $service->name ?></h2>

		<form id="updateUserInvoiceForm" action="/api/invoices/edit-user-data">
			<input type="hidden" name="id" value="<?= $model->id ?>">
			<div class="form-group -required">
				<label class="form-group_label" for="invoicesusersdata-identifier"><?= $model->getAttributeLabel('identifier') ?></label>
				<input type="text" id="invoicesusersdata-identifier" class="form-group_control" name="identifier" value="<?= $model->identifier ?>">
				<div class="form-group_help"></div>
			</div>
			<div class="form-group">
				<label class="form-group_label" for="invoicesusersdata-description"><?= $model->getAttributeLabel('description') ?></label>
				<input type="text" id="invoicesusersdata-description" class="form-group_control" name="description" value="<?= $model->description ?>">
				<div class="form-group_help"></div>
			</div>
			<?= Html::mtsButton('Узнать начисления') ?>
			<div class="create-user-invoice_delete-link-container">
				<a class="--user-invoice-delete" data-id="<?= $model->id ?>">Удалить</a>
			</div>
		</form>
	</div>
</div>
