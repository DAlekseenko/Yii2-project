<?php
/** @var $model \frontend\models\virtual\RechargeBalanceForm */

$this->title = 'МТС Деньги - Пополнение баланса';
$this->params['header'] = 'Пополнение баланса мобильного телефона';
$context = $this->context;
$context->getBreadcrumbsLayout()->appendBreadcrumb('Пополнение баланса');
?>
<div id="rechargeFormContainer" class="<?= $this->context->isMobile ? '-mobile-padding' : '' ?>">
	<?= $this->context->renderPartial('//partial/user/_recharge-form', ['model' => $model]) ?>
</div>
