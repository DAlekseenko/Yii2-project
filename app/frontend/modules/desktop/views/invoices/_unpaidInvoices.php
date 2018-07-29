<?php
/* @var $this yii\web\View
 * @var \common\models\Invoices[] $unpaidInvoices
 */
use common\helpers\Html;
use common\components\services\Helper;
?>

<?php if ($unpaidInvoices): $billSum = $generalSum = 0; ?>
	<table id="unpaidInvoicesContainer" class="mts-table">
		<tbody>
		<?php foreach ($unpaidInvoices as $defaultInvoice): $billSum += $defaultInvoice->getSum();
			$generalSum += $defaultInvoice->getTotalSum() ?>
			<tr class="mts-table_row -unpaid-invoices --invoice-row" data-invoice-id="<?= $defaultInvoice->id ?>" data-bill-sum="<?= $defaultInvoice->getSum() ?>" data-general-sum="<?= $defaultInvoice->getTotalSum() ?>">
				<td class="mts-table_cell -checkbox"><?= Html::mtsCheckbox('invoices[]', true, ['class' => '--invoices-check']) ?></td>
				<td class="mts-table_cell -unpaid-invoice-name" colspan="2"><a class="-dashed --show-description"><?= empty($defaultInvoice->params) ? $defaultInvoice->service->name : $defaultInvoice->params ?></a></td>
				<td class="mts-table_cell -sum">
					<h3><?= Helper::sumFormat($defaultInvoice->getTotalSum()) ?> <?= GLOBAL_CURRENCY ?></h3>
					<h5>оплата с комиссией 0 <?= GLOBAL_CURRENCY ?></h5>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
		<tfoot>
			<tr class="mts-table_row -unpaid-invoices -footer">
				<td class="mts-table_cell -checkbox"><?= Html::mtsCheckbox('invoices[]', true, ['class' => '--invoices-check-all']) ?></td>
				<td>
					<h5 class="-pb3">Сумма к оплате</h5>
					<h3><span id="generalSum"><?= Helper::sumFormat($generalSum) ?></span> <?= GLOBAL_CURRENCY ?></h3>
				</td>
				<td>
					<h5 class="-pb3">Без учета комиссии</h5>
					<h3><span id="billSum"><?= Helper::sumFormat($billSum) ?></span> <?= GLOBAL_CURRENCY ?></h3>
				</td>
				<td>
					<h5 class="-pb3">Размер комиссии</h5>
					<h3><span id="commissionSum"><?= Helper::sumFormat($generalSum) ?></span> <?= GLOBAL_CURRENCY ?></h3>
				</td>
			</tr>
		</tfoot>
	</table>
	<?php foreach ($unpaidInvoices as $defaultInvoice): ?>
		<script id="advanced_<?= $defaultInvoice->id ?>" type="text/template">
			<div class="payment-result-list">
				<div class="payment-result-list_field">
					<h5 class="-pb3" title="Услуга">Услуга</h5>
					<h3><?= $defaultInvoice->service->name ?></h3>
				</div>
				<div class="payment-result-list_field">
					<h5 class="-pb3" title="Категория">Категория</h5>
					<h3><?= htmlspecialchars($defaultInvoice->service->category['name'])?></h3>
				</div>
				<?php foreach($defaultInvoice->transaction->getFieldsMap() as $field): if (isset($field['name'], $field['value'])): ?>
					<div class="payment-result-list_field">
						<h5 class="-pb3" title="<?=htmlspecialchars($field['name'])?>"><?= htmlspecialchars($field['name']) ?></h5>
						<h3><?= htmlspecialchars($field['value']) ?></h3>
					</div>
				<?php endif; endforeach;?>
			</div>
		</script>
	<?php endforeach; ?>
	<div>
		<?=Html::mtsButton('Оплатить', ['id' => 'invoicePayFromMobile', 'class' => '-invoice-pay-button'])?>
		<h5 id="unpaidInvoicesHint" class="unpaid-invoices_hint"></h5>
	</div>
<?php else: ?>
	<div class="mts-highlighted-row">
		На данный момент у вас нет неоплаченных счетов
	</div>
	<a id="unpaidInvoicesRefresh" class="roller"></a>
<?php endif; ?>