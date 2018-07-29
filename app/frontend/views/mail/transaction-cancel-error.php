<?php
/** @var $paymentId int */
/** @var $request array */
/** @var $result array */
?>
Время: <?= date('Y-m-d H:i:s', time()) ?>
<hr>PaymentId:<hr>
<pre><?= $paymentId ?></pre>
<hr>Запрос ЕРИП:<hr>
<pre><? print_r($request); ?></pre>
<hr>Ответ ЕРИП:<hr>
<pre><? print_r($result); ?></pre>
