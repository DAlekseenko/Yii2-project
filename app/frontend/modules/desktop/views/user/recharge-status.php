<?php
/**
 * @var bool $statusOk
 * @var \common\models\AssistTransactions $at
 * @var \common\models\PaymentTransactions[]  $transactions
 */
$plural = count($transactions) > 1;
?>
<div class="status-page">
	<img class="status-page_img" src="/css/img/<?= isset($statusOk) ? 'payment-ok.png' : 'payment-fail.png' ?>">
	<h1 class="status-page_header"><?= isset($statusOk) ? 'Завершено успешно' : 'Отказ в авторизации' ?></h1>
	<? if (isset($statusOk)): ?>
	<div class="status-page_description --desc" <?= empty($transactions) ? '' : 'data-success="Оплата ' . ($plural ? 'счетов' : 'услуги') . ' прошла успешно" data-fail="Баланс мобильного телефона будет пополнен в ближайшее время. При поступлении средств на счет мобильного телефона, пожалуйста, повторите попытку оплаты"' ?> >
		<?= empty($transactions) ?
			'Пополнение мобильного телефона с карты' :
			'Списание с карты прошло успешно, ожидайте пополнения баланса мобильного телефона и оплату ' . ($plural ? 'счетов' : 'услуги') . '. По факту оплаты ' . ($plural ? 'счетов' : 'услуги') . ' вы получите СМС-сообщение'
		?>
	</div>
	<? else: ?>
	<div class="status-page_description">Пополнение мобильного телефона с карты</div>
	<? endif; ?>
	<div class="status-page_info-block --status-result" data-transaction-key="<?= $at->order_number ?>"></div>
</div>
<script id="infoTemplate" type="text/template">
	<div class="payment-result">
		<div class="payment-result_item -w-37">
			<h5 class="-pb3">Сумма операции</h5>
			<h3>{{code.sum}} {{code.currency}}</h3>
		</div>
		<div class="payment-result_item -w-37">
			<h5 class="-pb3">Уникальный номер операции</h5>
			<h3>{{code.billnumber}}</h3>
		</div>
		<div class="payment-result_item -w-26">
			<h5 class="-pb3">Дата операции</h5>
			<h3>{{code.orderdate}}</h3>
		</div>
	</div>
	<div class="status-page_advanced"><a class="status-page_advanced-text -dotted --advanced" data-hide-text="Показать детали" data-show-text="Скрыть детали"></a></div>
	<div class="payment-result">
		<div class="payment-result_item -w-37">
			<h5 class="-pb3">Код авторизации</h5>
			<h3>{{code.approvalcode}}</h3>
		</div>
		<div class="payment-result_item -w-37">
			<h5 class="-pb3">Держатель карты</h5>
			<h3>{{code.cardholder}}</h3>
		</div>
		<div class="payment-result_item -w-26">
			<h5 class="-pb3">Платежное средство</h5>
			<h3>{{code.meannumber}} {{code.meantypename}}</h3>
		</div>
		<div class="payment-result_item -w-37">
			<h5 class="-pb3">Номер транзакции</h5>
			<h3>{{code.order_number}}</h3>
		</div>
		<div class="payment-result_item -w-37">
			<h5 class="-pb3">Покупатель</h5>
			<h3>{{code.firstname}} {{code.lastname}}</h3>
		</div>
		<div class="payment-result_item -w-26">
			<h5 class="-pb3">Детали платежа</h5>
			<h3>{{code.message}}</h3>
		</div>
	</div>
	<div class="status-page_footer">
		<? if (isset($statusOk)): ?>
		{{#if with_services}}
			<div class="status-page_footer_text">
			{{#if pay_services_result}}
				Чтобы посмотреть квитанцию об оплате, нажмите «Продолжить»
			{{/if}}
			</div>
		{{/if}}
		<? endif; ?>
		<div><a href="{{redirect}}"><?= \common\helpers\Html::mtsButton('Продолжить') ?></a></div>
	</div>
</script>
