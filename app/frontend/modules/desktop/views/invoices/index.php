<?php

/* @var $this yii\web\View
 * @var string $unpaidInvoices
 */
use common\helpers\Html;

$context = $this->context;
$this->title = 'МТС Деньги - Мои счета на оплату';
$this->params['header'] = 'Мои счета на оплату';
$context->getBreadcrumbsLayout()->appendBreadcrumb('Мои счета на оплату');
?>
<main>
	<section id="unpaidInvoices" class="unpaid-invoices">
		<?= $unpaidInvoices ?>
	</section>
	<div id="invoicesSlider" class="invoices-slider-container"></div>
	<h2 class="invoices-header">Введите номера лицевых счетов и оплачивайте выставленные счета по мере появления.</h2>

	<div class="invoices-actions">
		<?=Html::mtsButton('Добавить услугу', ['id' => 'invoicesAddService', 'class' => '-invoice-add-button'])?>
	</div>
	<section id="userInvoices" class="invoices -mb25"></section>
</main>
<script id="userInvoicesTemplate" type="text/template">
	{{#each data}}
	<div class="user-invoices_row -folder --user-invoice-row" data-type="folder" data-category-id="{{id}}" data-key="{{key}}">
		<h2 class="user-invoices_cell -invoice-name">
			{{name}}
		</h2>
		<h3 class="user-invoices_cell -identifier">
			<a class="-dashed --user-invoice-identifier">{{#if placeholder}}{{placeholder}}{{else}}Узнать начисления{{/if}}</a>
		</h3>
		<div class="user-invoices_cell -action --cell-action -wide">
			{{#if is_global}} {{#unless has_children}}
			<a class="cross-gray --action-delete"></a>
			{{/unless}}{{/if}}
		</div>
	</div>
		{{#each services}}
		<div class="user-invoices_row -subrow --user-invoice-row" data-type="user-invoice" data-id="{{id}}">
			<h5 class="user-invoices_cell -invoice-name">
				{{#if description}}
				<div class="-pb3"><a class="-dashed --user-invoice-update">{{description}}</a></div>{{name}}
				{{else}}
				<div class="-pb3"><a class="-dashed --user-invoice-update">{{category_name}}</a></div>{{service_name}}
				{{/if}}
			</h5>
			<h3 class="user-invoices_cell -identifier -word-break-all">
				<span class="-color-text-gray">№</span> {{identifier}}
			</h3>
			<div class="user-invoices_cell -action -wide">
				<a class="user-invoice_pay-button" href="/payments/pay?id={{service_id}}&invoice={{id}}"></a>
			</div>
		</div>
		{{/each}}
	{{/each}}
</script>