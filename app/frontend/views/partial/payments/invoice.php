<?php
/**
 * @var $paymentHistoryItem \common\models\PaymentTransactions
 */
use common\components\services\Helper;

$s = [
	'invoice_header' => '
		font-size: 22px;
		color: #F44336;
	',
	'invoice_content' => '
		font-family: \'Courier New\';
		font-size: 14px;
		width: 600px;
		margin: 30px 10px;
	',
	'invoice_line' => '
		margin-top: 2px;
	',
	'-align-right' => '
		float: right;
	',
	'-margin-bottom' => '
		margin-bottom: 14px;
	'

];

$eripOperationNumber = $paymentHistoryItem->getEripDataArray('eripResult.eripOperationNumber');
$eripPayerCode = $paymentHistoryItem->getEripDataArray('eripResult.eripPayerCode');
$currency = $paymentHistoryItem->getCurrency();
$receiver = $paymentHistoryItem->getEripDataArray('paymentInfo.receiver');
$serverName = $paymentHistoryItem->getEripDataArray('paymentInfo.serverName');
$serverTime = $paymentHistoryItem->getEripDataArray('paymentInfo.serverTime') ?: $paymentHistoryItem->getEripDataArray('eripResult.date');
$sender = $paymentHistoryItem->getEripDataArray('paymentInfo.sender');
?>
<div>
	<div style="<?=$s['invoice_header']?>">Ваш счет</div>
	<div style="<?=$s['invoice_content']?>">
		<div style="<?=$s['invoice_line']?>">Дата платежа: <span style="<?=$s['-align-right']?>"><?= date('d/m/Y H:i:s', strtotime($paymentHistoryItem->date_pay))?></span></div>
		<div style="<?=$s['invoice_line']?>">Номер плательщика ЕРИП: <?= $eripPayerCode ?></span></div>
		<div style="<?=$s['invoice_line']?>">Номер абонента МТС: <?=Helper::formatPhone($paymentHistoryItem['user']['phone'], Helper::FORMAT_SIMPLE)?></span></div>
		<div style="<?=$s['invoice_line']?>">Время сервера <?=$serverName?>: <?= date('d.m.Y H:i:s', strtotime($paymentHistoryItem->bank_date_create)) ?></span></div>
		<div style="<?=$s['invoice_line']?>">Отправитель платежа: <?=$sender?></span></div>
		<div style="<?=$s['invoice_line']?>">Получатель платежа: <?=$receiver?></span></div>
		<div style="<?=$s['invoice_line']?>"><center><?=$paymentHistoryItem->service->category->name?> / <?=$paymentHistoryItem->service->name?></center></div>
		<?php foreach($paymentHistoryItem->getFieldsMap() as $i => $field): if (isset($field['name'], $field['value'])): ?>
			<div style="<?=$s['invoice_line']?>"><?= htmlspecialchars($field['name']) ?>: <span style="<?=$s['-align-right']?>"><?= htmlspecialchars($field['value']) ?></span></div>
		<?php endif; endforeach;?>
		<div style="<?=$s['invoice_line']?>">Сумма: <span style="<?=$s['-align-right']?>"><?= Helper::sumFormat($paymentHistoryItem->getSum()) . ' ' . $currency ?></span></div>
		<div style="<?=$s['invoice_line']?>">Сумма вознаграждения: <span style="<?=$s['-align-right']?>"><?= Helper::sumFormat($paymentHistoryItem->getCommission()) . ' ' . $currency ?></span></div>
		<div style="<?=$s['invoice_line']?> <?=$s['-margin-bottom']?>">Сумма всего: <span style="<?=$s['-align-right']?>"><?= Helper::sumFormat($paymentHistoryItem->sum) . ' ' . $currency ?></span></div>
		<?php if ($eripOperationNumber): ?>
			<div style="<?=$s['invoice_line']?>">N операции в ЕРИП: <?= $eripOperationNumber ?></div>
		<?php endif; ?>
		<div style="<?=$s['invoice_line']?>">Тел. ЕРИП для справок: 141</div>
	</div>
	<div>С уважением,<br>
		СООО "Мобильные ТелеСистемы"
	</div>
</div>
