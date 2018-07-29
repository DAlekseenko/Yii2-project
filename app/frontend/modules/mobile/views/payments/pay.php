<?php
/**
 * @var $service common\models\Services
 * @var $hasAccept bool
 * @var $favorite \common\models\PaymentFavorites|null
 */
use eripDialog\EdHelper as H;
use common\helpers\Html;

$this->title = 'МТС Деньги - Все платежи - ' . $service->name;
$context = $this->context;
$context->getHeaderLayout()->setTemplate('_header')->setVars(['service' => $service]);
$context->getBreadcrumbsLayout()->setBreadcrumbs($service['category']->getBreadcrumbs())->appendBreadcrumb($service->name);
?>
<script id="paymentFormRowTemplate" type="text/template">
	{{#if editable}}
	<div class="form-group -payments -with-status">
		<label class="form-group_label" for="sumId">{{description}}</label>
		{{{control}}}
		<div class="form-group_help"></div>
		{{#if hint}}
		<div class="form-group_hint -dialog-form">{{hint}}</div>
		{{/if}}
		<div class="form-group_field-loading"></div>
	</div>
	{{else}}
	<div class="form-group -payments -info">
		<span class="form-group_label">{{description}}</span>
		<div type="text" class="form-group_pseudo-control">{{value}}</div>
	</div>
	{{/if}}
</script>
<script id="paymentSummaryTemplate" type="text/template">
	<div class="form-group -payments -info -summary">
		<div type="text" class="form-group_pseudo-control">{{summary}}</div>
	</div>
</script>
<form lang="en-GB" id="onFormSubmit_paymentForm" method="get" class="payments-form -loading -mobile-padding" data-dialog-url="/api/erip-dialog?" data-name="<?= $service->name ?>" data-redirect-url="/payments/history-item?id=">
	<div class="payments-form_loader"><img src="/css/img/roller.gif"></div>
	<input type="hidden" name="<?= H::F_SERVICE_CODE ?>" value="<?= $service->id ?>">
	<input type="hidden" name="<?= H::F_MODE ?>" value="start">
	<input type="hidden" name="<?= H::F_MTS_MONEY_SESSION ?>" value="">
	<div class="error-message"></div>
	<fieldset class="dialog-fieldset" id="dialogFields" <?= empty($fields) ? '' : 'data-fields="' . htmlspecialchars($fields) . '"' ?>></fieldset>
	<h5 class="subscription_rules" data-error="Для проведения платежа необходимо подтвердить согласие на подключение услуги «МТС Деньги»">
		<div class="subscription_rules_text">
		<div class="rules_agree-box_wrap"><?= Html::mtsCheckbox('agree', false, ['class' => 'rules_agree-box']) ?></div>
			Нажимая «Подтвердить&nbsp;платеж», Вы подтверждаете, что ознакомлены и согласны с <a href="/help/user-agreement" target="_blank">Правилами&nbsp;системы</a> и подключением услуги «МТС&nbsp;Деньги». <span class="rules_agree-info"></span>
		</div>
	</h5>
	<? if (\yii::$app->user->isGuest): ?>
		<h5 class="rules">Нажимая кнопку «Подтвердить&nbsp;платеж», вы подтверждаете, что ознакомлены и согласны с <a href="/help/user-agreement" target="_blank">Правилами&nbsp;системы</a></h5>
	<? endif; ?>
	<div class="button-wrap">
	    <button type="submit" class="mts-button -payments-mts-button"><span class="-next">Далее</span><span class="-send">Подтвердить платеж</span></button>
	</div>
	<? if ($hasAccept): ?>
		<div class="accept-info">
			После нажатия на кнопку «Подтвердить платеж» для завершения оплаты отправьте, пожалуйста, ответное бесплатное SMS-сообщение на номер 5000.
		</div>
	<? endif; ?>
</form>
