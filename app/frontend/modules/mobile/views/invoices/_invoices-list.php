<?php
/**
 * @var $this yii\web\View
 * @var \common\models\PaymentTransactions[] $transactions
 * @var
 */
use common\components\services\Helper;

$result = [
	'totalSum' => 0,
	'sum' => 0,
	'commission' => 0,
];
?>
<div class="payment-result-content">
	<?php foreach ($transactions as $tr): ?>
		<?php $result['totalSum'] += $tr->sum; ?>
		<?php $result['sum'] += $tr->getSum(); ?>
		<?php $result['commission'] += $tr->getCommission(); ?>
		<div class="payment-result -col-2 -mobile-padding">
			<div class="payment-result_item -full-row">
				<h5 class="-pb3">Услуга</h5>
				<h3><?= $tr['service']['name'] ?></h3>
			</div>
			<div class="payment-result_item -full-row">
				<h5 class="-pb3">Категория</h5>
				<h3><?= $tr['service']['category']['name'] ?></h3>
			</div>
			<div class="payment-result_item">
				<h5 class="-pb3">Город</h5>
				<h3>
					<?php if ($tr['service']['location']): ?>
						<?= $tr['service']['location']['name'] ?>
					<?php else: ?>
						<i>(Нет)</i>
					<?php endif; ?>
				</h3>
			</div>
			<div class="payment-result_item">
				<h5 class="-pb3">Сумма к оплате</h5>
				<h3><?= Helper::sumFormat($tr->sum) . ' ' . $tr->getCurrency() ?></h3>
			</div>
			<div class="payment-result_item">
				<h5 class="-pb3">Без учета комиссии</h5>
				<h3><?= Helper::sumFormat($tr->getSum()) . ' ' . $tr->getCurrency() ?></h3>
			</div>
			<div class="payment-result_item">
				<h5 class="-pb3">Размер комиссии</h5>
				<h3><?= Helper::sumFormat($tr->getCommission()) . ' ' . $tr->getCurrency() ?></h3>
			</div>
			<?php foreach ($tr->getFieldsMap() as $i => $field): if (isset($field['name'], $field['value'])): ?>
				<div class="payment-result_item">
					<h5 class="-pb3"><?= htmlspecialchars($field['name']) ?></h5>
					<h3><?= htmlspecialchars($field['value']) ?></h3>
				</div>
			<?php endif; endforeach; ?>
		</div>
	<?php endforeach; ?>
	<div class="payment-result -col-2 -mobile-padding">
		<div class="payment-result_item">
			<h5 class="-pb3">Сумма к оплате</h5>
			<h3><?= Helper::sumFormat($result['totalSum']) . ' ' . $tr->getCurrency() ?></h3>
		</div>
		<div class="payment-result_item">
			<h5 class="-pb3">Без учета комиссии</h5>
			<h3><?= Helper::sumFormat($result['sum']) . ' ' . $tr->getCurrency() ?></h3>
		</div>
		<div class="payment-result_item">
			<h5 class="-pb3">Размер комиссии</h5>
			<h3><?= Helper::sumFormat($result['commission']) . ' ' . $tr->getCurrency() ?></h3>
		</div>
	</div>
</div>
