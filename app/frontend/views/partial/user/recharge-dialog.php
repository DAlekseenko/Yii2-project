<?php
/**
 * @var $form string
 * @var \common\models\PaymentTransactions $transaction
 */
$this->title = 'МТС Деньги - Оплата услуги';
$this->params['header'] = 'Оплата услуги';
$context = $this->context;
$context->getBreadcrumbsLayout()->appendBreadcrumb('Оплата услуги');
?>
<? if (Yii::$app->session->hasFlash('payErrorMessage')): ?>
	<div class="error-message -mobile-padding">
		<?= Yii::$app->session->getFlash('payErrorMessage') ?>
	</div>
<? else: ?>
	<?= $this->context->renderPartial('../invoices/_invoices-list', ['transactions' => [$transaction]]) ?>
	<?= $form ?>
<? endif; ?>
