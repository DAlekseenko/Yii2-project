<?php
/** @var $paymentHistoryItem \backend\models\PaymentTransactions */
?>
<div class="mts-highlighted-row -bg-color-<?= strtolower($paymentHistoryItem->status) ?>">
	<?= $paymentHistoryItem->getStatusName() ?>
</div>
