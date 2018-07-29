<?php
/* @var $this yii\web\View
 * @var \common\models\Invoices[] $unpaidInvoices
 */
use common\helpers\Html;
use common\components\services\Helper;

if ($unpaidInvoices): $billSum = $generalSum = 0; ?>
	<div id="unpaidInvoicesContainer" class="unpaid-invoices-container">
		<?php foreach ($unpaidInvoices as $defaultInvoice): $billSum += $defaultInvoice->getSum();
			$generalSum += $defaultInvoice->getTotalSum() ?>
			<div class="unpaid-invoices_row --invoice-row -mobile-padding" data-invoice-id="<?= $defaultInvoice->id ?>" data-bill-sum="<?= $defaultInvoice->getSum() ?>" data-general-sum="<?= $defaultInvoice->getTotalSum() ?>">
				<div class="unpaid-invoices_left">
					<?= Html::mtsCheckbox('invoices[]', true, ['class' => '--invoices-check']) ?>
				</div>
				<div class="unpaid-invoices_right">
					<div class="unpaid-invoices_service-name">
						<a class="-dashed --show-description"><?= $defaultInvoice->service->name ?></a>
					</div>

					<div class="unpaid-invoices_description --invoice-description">
						<div class="unpaid-invoices_description-row">
							<h5 class="-pb3" title="Категория">Категория</h5>
							<h3><?= htmlspecialchars($defaultInvoice->service->category['name'])?></h3>
						</div>
						<?php foreach($defaultInvoice->transaction->getFieldsMap() as $field): if (isset($field['name'], $field['value'])): ?>
							<div class="unpaid-invoices_description-row">
								<h5 class="-pb3" title="<?=htmlspecialchars($field['name'])?>"><?= htmlspecialchars($field['name']) ?></h5>
								<h3><?= htmlspecialchars($field['value']) ?></h3>
							</div>
						<?php endif; endforeach;?>
					</div>

					<h3 class="-pb3"><?= Helper::sumFormat($defaultInvoice->getTotalSum()) ?> <?= GLOBAL_CURRENCY ?></h3>
					<h5>оплата с комиссией 0 <?= GLOBAL_CURRENCY ?></h5>
				</div>
			</div>
		<?php endforeach; ?>
		<div class="unpaid-invoices_row -footer -mobile-padding">
			<div class="unpaid-invoices_left">
				<?= Html::mtsCheckbox('invoices[]', true, ['class' => '--invoices-check-all']) ?>
			</div>
			<div class="unpaid-invoices_right">
				<div class="unpaid-invoices_description-row">
					<h5 class="-pb3">Сумма к оплате</h5>
					<h3><span id="generalSum"><?= Helper::sumFormat($generalSum) ?></span> <?= GLOBAL_CURRENCY ?></h3>
				</div>
				<div class="unpaid-invoices_description-row">
					<h5 class="-pb3">Без учета комиссии</h5>
					<h3><span id="billSum"><?= Helper::sumFormat($billSum) ?></span> <?= GLOBAL_CURRENCY ?></h3>
				</div>
				<div class="unpaid-invoices_description-row">
					<h5 class="-pb3">Размер комиссии</h5>
					<h3><span id="commissionSum"><?= Helper::sumFormat($generalSum - $billSum) ?></span> <?= GLOBAL_CURRENCY ?></h3>
				</div>
			</div>
		</div>
	</div>
	<div class="-mobile-padding">
		<?=Html::mtsButton('Оплатить', ['id' => 'invoicePayFromMobile', 'class' => '-invoice-pay-button'])?>
		<h5 id="unpaidInvoicesHint" class="unpaid-invoices_hint"></h5>
	</div>
<?php else: ?>
	<div class="unpaid-invoices-empty -mobile-padding">
		<div class="mts-highlighted-row">
			На данный момент у вас нет счетов
		</div>
		<a id="unpaidInvoicesRefresh" class="roller"></a>
	</div>
<?php endif; ?>