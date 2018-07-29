<?php
/**
 * @var $this yii\web\View
 * @var \common\models\PaymentTransactions[] $transactions
 * @var string $form
 */

$context = $this->context;
$this->title = 'МТС Деньги - Оплатить счета';
$this->params['header'] = 'Оплатить счета';
$context->getBreadcrumbsLayout()
		->appendBreadcrumb('Мои счета на оплату', '/invoices')
		->appendBreadcrumb('Оплатить счета');
?>
<? if (Yii::$app->session->hasFlash('payErrorMessage')): ?>
	<div class="error-message -mobile-padding">
		<?= Yii::$app->session->getFlash('payErrorMessage') ?>
	</div>
<? elseif($transactions): ?>
	<?= $this->context->renderPartial('_invoices-list', ['transactions' => $transactions]) ?>
	<?= $form ?>
<? endif; ?>
