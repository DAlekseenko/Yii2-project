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

	<div class="user-invoices">
		<div class="-mobile-padding">
			<h2 class="invoices-header">Настройте идентификаторы лицевых счетов и оплачивайте счета по мере их появления.</h2>
		</div>

		<div class="-mobile-padding">
			<a href="/invoice-search/index">
				<?=Html::mtsButton('Добавить')?>
			</a>
		</div>
		<section id="userInvoices" class="invoices -mb25"></section>
	</div>
</main>
<script id="userInvoicesTemplate" type="text/template">
	{{#each data}}
	<div class="user-invoices_row -folder --user-invoice-row" data-type="folder" data-category-id="{{id}}"  data-key="{{key}}">
		<div class="user-invoices_cell">
			<h2>{{name}}</h2>
			<h3>
				<a class="-dashed" href="/invoice-search/category?categoryId={{id}}">{{#if placeholder}}{{placeholder}}{{else}}Узнать начисления{{/if}}</a>
			</h3>
		</div>
		<div class="user-invoices_cell -action --cell-action">
			{{#if is_global}} {{#unless has_children}}
			<a class="cross-gray --action-delete"></a>
			{{/unless}}{{/if}}
		</div>
	</div>
		{{#each services}}
		<div class="user-invoices_row -subrow --user-invoice-row" data-type="user-invoice" data-id="{{id}}">
			<h5 class="user-invoices_cell -invoice-name">
				<div class="-pb3">
					<a class="-dashed" href="/invoices/update-user-invoice?id={{id}}">{{#if description}}{{description}}{{else}}{{category_name}}{{/if}}</a>
				</div>
				{{service_name}}
				<div class="user-invoices_identifier-text"><span class="-color-text-gray">№</span>&nbsp;{{identifier}}</div>
			</h5>
			<div class="user-invoices_cell -action -wide">
				<a class="user-invoice_pay-button" href="/payments/pay?id={{service_id}}&invoice={{id}}"></a>
			</div>
		</div>
		{{/each}}
	{{/each}}
</script>