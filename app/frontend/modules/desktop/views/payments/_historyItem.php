<?php
/**
 * @var $this yii\web\View
 * @var $paymentHistoryItem \common\models\PaymentTransactions
 * @var $sendMailForm \frontend\models\virtual\SendMailForm
 * @var $addFavorite \frontend\models\virtual\AddFavorite
 * @var $context \yii\web\Controller|\frontend\modules\desktop\components\behaviors\RenderLayout
 */

use common\helpers\Html;
use common\components\services\Helper;

$date = date('d.m.Y H:i', strtotime($paymentHistoryItem->date_create));
?>

<div class="payment-result --history-item-content">
	<div class="payment-result_item -w-37">
		<span><?= $this->render('//partial/payments/_statusPart', ['paymentHistoryItem' => $paymentHistoryItem]) ?></span>
		<span class="roller --refreshContent" data-url="/payments/refresh-history-item?id=<?= $paymentHistoryItem->getTransactionKey() ?>"></span>
	</div>
	<div class="payment-result_item -align_right -w-63 -right">
		<?php if (!Yii::$app->user->isGuest): ?>
			<a class="add-to-favorite -dashed -with-star --favorite-add" data-transaction-id="<?= $paymentHistoryItem->getTransactionKey() ?>" data-item-name="<?=$paymentHistoryItem->item_name?>" data-pos-x="left">Добавить в избранные платежи</a>
		<?php endif; ?>
	</div>
	<div class="-clr"></div>
	<div class="payment-result_item">
		<h5 class="-pb3">Услуга</h5>
		<h3><?= $paymentHistoryItem->service->name ?></h3>
	</div>
	<div class="-clr"></div>
	<div class="payment-result_item -w-37">
		<h5 class="-pb3">Город</h5>
		<h3><?= isset($paymentHistoryItem->service->location->name) ? $paymentHistoryItem->service->location->name : '<i>нет</i>' ?><h3>
	</div>
	<div class="payment-result_item -w-63 -right">
		<h5 class="-pb3">Категория</h5>
		<h3><?= $paymentHistoryItem->service->category->name ?><h3>
	</div>
	<div class="-clr"></div>
	<div class="payment-result_item -w-37">
		<h5 class="-pb3">Дата проведения</h5>
		<h3><?= $date ?></h3>
	</div>
	<div class="payment-result_item -w-63 -right">
		<h5 class="-pb3">Код транзакции</h5>
		<h3><?= $paymentHistoryItem->uuid ?></h3>
	</div>
	<div class="-clr"></div>
	<div class="payment-result_item -w-37">
		<h5 class="-pb3">Сумма без комиссии</h5>
		<h3><?= Helper::sumFormat($paymentHistoryItem->getSum()) . ' ' . $paymentHistoryItem->getCurrency() ?></h3>
	</div>
	<div class="payment-result_item -w-37">
		<h5 class="-pb3">Размер комиссии</h5>
		<h3><?= Helper::sumFormat($paymentHistoryItem->getCommission()) . ' ' . $paymentHistoryItem->getCurrency() ?></h3>
	</div>
	<div class="payment-result_item -w-26 -right">
		<h5 class="-pb3">Итоговая сумма</h5>
		<h3><?= Helper::sumFormat($paymentHistoryItem->sum) . ' ' . $paymentHistoryItem->getCurrency() ?></h3>
	</div>
	<div class="-clr"></div>
	<?php foreach($paymentHistoryItem->getFieldsMap() as $i => $field): if (isset($field['name'], $field['value'])): ?>
		<div class="payment-result_item <?=$i % 3 == 2 ? '-w-26 -right' : '-w-37'?>">
			<h5 class="-pb3"><?= htmlspecialchars($field['name']) ?></h5>
			<h3><?= htmlspecialchars($field['value']) ?></h3>
		</div>
		<?php if ($i % 3 == 2): ?>
			<div class="-clr"></div>
		<?php endif; ?>
	<?php endif; endforeach;?>
	<div class="-clr"></div>
	<? if ($paymentHistoryItem->status == $paymentHistoryItem::STATUS_SUCCESS): ?>
	<div class="payment-result_item -w-37 --send-mail-form">
		<h5 class="-pb3">E-mail адрес</h5>
		<div>
			<?= $this->render('//partial/site/sendInvoiceForm', [
				'sendMailForm' => $sendMailForm,
				'id' => $paymentHistoryItem->getTransactionKey(),
			]) ?>
		</div>
	</div>
	<div class="payment-result_item -no-title -w-63 -right -align_right --print-result" data-transaction-id="<?= $paymentHistoryItem->getTransactionKey() ?>">
		<?= Html::mtsButton('Распечатать', ['class' => 'mts-button-print']) ?>
	</div>

	<script id="printResultTemplate_<?= $paymentHistoryItem->getTransactionKey() ?>" type="text/template">
		<?= $this->render('//partial/payments/invoiceWrap', ['paymentHistoryItem' => $paymentHistoryItem]) ?>
	</script>
	<? endif; ?>
</div>