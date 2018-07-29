<?php
$isPayments = $this->context->id == 'payments';
$action = $this->context->action->id;
?>
<div class="user-menu">
	<div class="user-menu_link<?= $this->context->id == 'invoices' ? ' -act' : '' ?>">
		<a href="<?= \yii\helpers\Url::to('/invoices') ?>">Мои счета на оплату (<?=\common\models\Invoices::findActive()->count();?>)</a>
	</div>
	<div class="user-menu_link<?= $isPayments && $action == 'index' ? ' -act' : '' ?>">
		<a href="<?= \yii\helpers\Url::to('/payments') ?>">Все платежи</a>
	</div>
	<div class="user-menu_link<?= $isPayments && $action == 'favorites' ? ' -act' : '' ?>">
		<a href="<?= \yii\helpers\Url::to('/payments/favorites') ?>">Избранные платежи</a>
	</div>
	<div class="user-menu_link<?= $isPayments && $action == 'history' ? ' -act' : '' ?>">
		<a href="<?= \yii\helpers\Url::to('/payments/history') ?>">История платежей</a>
	</div>
	<div class="user-menu_link<?= $this->context->id == 'settings' ? ' -act' : '' ?>">
		<a href="<?= \yii\helpers\Url::to('/settings') ?>">Настройки</a>
	</div>
	<div class="user-menu_link">
		<a id="logoutLink" data-csrf="<?= Yii::$app->request->getCsrfToken() ?>">Выйти</a>
	</div>
</div>