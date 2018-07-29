<?php
/**
 * @var $paymentHistoryItem \common\models\PaymentTransactions
 */
?>
<!DOCTYPE html>
<head>
	<title>Оплата услуги «<?= $paymentHistoryItem->service->name ?>» <?= date('d.m.Y H:i', strtotime($paymentHistoryItem->date_create)) ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
<?= $this->render('//partial/payments/invoice', ['paymentHistoryItem' => $paymentHistoryItem]) ?>
</body>
