<?php
/**
 * @var $this yii\web\View
 * @var $paymentHistoryItem \common\models\PaymentTransactions
 * @var $sendMailForm \frontend\models\virtual\SendMailForm
 * @var $addFavorite \frontend\models\virtual\AddFavorite
 * @var $context \yii\web\Controller|\frontend\modules\desktop\components\behaviors\RenderLayout
 */

use common\components\services\Helper;

$date = date('d.m.Y H:i', strtotime($paymentHistoryItem->date_create));
?>

<div class="payment-result -col-2 --history-item-content">
	<div class="payment-result_item -refresh -full-row">
		<span><?= $this->render('//partial/payments/_statusPart', ['paymentHistoryItem' => $paymentHistoryItem]) ?></span>
		<span class="roller --refreshContent" data-url="/payments/refresh-history-item?id=<?= $paymentHistoryItem->getTransactionKey() ?>"></span>
	</div>
	<div class="payment-result_item -full-row">
		<h5 class="-pb3">Услуга</h5>
		<h3><?= $paymentHistoryItem->service->name ?></h3>
	</div>
	<div class="payment-result_item -full-row">
		<h5 class="-pb3">Категория</h5>
		<h3><?= $paymentHistoryItem->service->category->name ?><h3>
	</div>
	<div class="payment-result_item">
		<h5 class="-pb3">Город</h5>
		<h3><?= isset($paymentHistoryItem->service->location->name) ? $paymentHistoryItem->service->location->name : '<i>нет</i>' ?><h3>
	</div>
	<div class="payment-result_item">
		<h5 class="-pb3">Дата проведения</h5>
		<h3><?= $date ?></h3>
	</div>
	<div class="payment-result_item">
		<h5 class="-pb3">Сумма без комиссии</h5>
		<h3><?= Helper::sumFormat($paymentHistoryItem->getSum()) . ' ' . $paymentHistoryItem->getCurrency() ?></h3>
	</div>
	<div class="payment-result_item">
		<h5 class="-pb3">Размер комиссии</h5>
		<h3><?= Helper::sumFormat($paymentHistoryItem->getCommission()) . ' ' . $paymentHistoryItem->getCurrency() ?></h3>
	</div>
	<div class="payment-result_item">
		<h5 class="-pb3">Итоговая сумма</h5>
		<h3><?= Helper::sumFormat($paymentHistoryItem->sum) . ' ' . $paymentHistoryItem->getCurrency() ?></h3>
	</div>
	<?php foreach($paymentHistoryItem->getFieldsMap() as $i => $field): if (isset($field['name'], $field['value'])): ?>
		<div class="payment-result_item">
			<h5 class="-pb3"><?= htmlspecialchars($field['name']) ?></h5>
			<h3><?= htmlspecialchars($field['value']) ?></h3>
		</div>
	<?php endif; endforeach;?>
	<? if ($paymentHistoryItem->status == $paymentHistoryItem::STATUS_SUCCESS): ?>
	<div class="payment-result_item -full-row --send-mail-form">
		<h5 class="-pb3">E-mail адрес</h5>
		<div>
			<?= $this->render('//partial/site/sendInvoiceForm', [
				'sendMailForm' => $sendMailForm,
				'id' => $paymentHistoryItem->getTransactionKey(),
			]) ?>
		</div>
	</div>

	<script id="printResultTemplate_<?= $paymentHistoryItem->getTransactionKey() ?>" type="text/template">
		<?= $this->render('//partial/payments/invoiceWrap', ['paymentHistoryItem' => $paymentHistoryItem]) ?>
	</script>
	<? endif; ?>
</div>