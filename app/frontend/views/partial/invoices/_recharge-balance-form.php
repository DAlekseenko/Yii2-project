<?
/**
 * @var $res
 * @var $sum
 * @var $need
 * @var \frontend\models\virtual\RechargeBalanceForm $rechargeModel
 * @var bool $validBalance
 * @var \api\components\services\Subscription\Entities\UserSubscriptionInfo $subscriptionInfo
 */
use common\components\widgets\ActiveForm;
use common\helpers\Html;

$form = ActiveForm::begin(['fieldConfig' => ['options' => ['class' => 'form-group -settings-form -without-mb']]]);
?>
<form class="pay-invoices-mobile" id="<?= isset($rechargeModel) && $rechargeModel->isComplete() ? 'bankForm' : 'rechargeForm' ?>" action="<?= isset($rechargeModel) ? $rechargeModel->getAction() : '' ?>" method="post">
<input type="hidden" name="_csrf" value="<?= Yii::$app->request->getCsrfToken() ?>">
<div class="settings-item<?= $this->context->isMobile ? ' -mobile-padding' : '' ?>">
	<div class="settings-handler">
		<a class="settings-handler_link -dashed --link">Данные плательщика</a>
	</div>
	<div class="settings-item-wrap --content" style="display:<?= $rechargeModel->hasErrors() || empty($rechargeModel->first_name) || empty($rechargeModel->last_name) || empty($rechargeModel->email)  ? 'block' : 'none' ?>">
		<div class="settings-item-content -invoice">
			<div class="pointer -settings-top"></div>
			<div class="--content-form">
				<div>
					<?= $form->field($rechargeModel, 'first_name', ['inputOptions' => ['class' => 'form-group_control'] + ($rechargeModel->isComplete() ? ['name' => 'Firstname'] : []) ]) ?>

					<?= $form->field($rechargeModel, 'last_name', ['inputOptions' => ['class' => 'form-group_control'] + ($rechargeModel->isComplete() ? ['name' => 'Lastname'] : []) ]) ?>

					<?= $form->field($rechargeModel, 'email', ['inputOptions' => ['class' => 'form-group_control'] + ($rechargeModel->isComplete() ? ['name' => 'Email'] : []) ]) ?>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="info-block">
	На балансе мобильного телефона недостаточно средств.<br>
	С учетом обязательного остатка в <b><?= number_format($res, 2) ?> <?= GLOBAL_CURRENCY ?></b> для проведения платежа на <b><?= number_format($sum, 2) ?> <?= GLOBAL_CURRENCY ?></b> не хватает <b><?= number_format($need, 2) ?> <?= GLOBAL_CURRENCY ?></b>.<br>
	<? if ($validBalance): ?>
		Проведите платеж, пополнив баланс мобильного телефона с банковской карты на:
		<div class="recharge-sum-radiobox"><?= \common\helpers\Html::mtsRadiobox($rechargeModel->isComplete() ? 'OrderAmount' : 'RechargeBalanceForm[sum]', true, ['value' => $need, 'label' => number_format($need, 2) . ' ' . GLOBAL_CURRENCY])?></div>
		<div class="recharge-sum-radiobox"><?= \common\helpers\Html::mtsRadiobox($rechargeModel->isComplete() ? 'OrderAmount' : 'RechargeBalanceForm[sum]', (string)$rechargeModel->sum == (string)$sum, ['value' => $sum, 'label' => number_format($sum, 2) . ' ' . GLOBAL_CURRENCY])?></div>
	<? else: ?>
		Проведите платеж, пополнив баланс мобильного телефона с банковской карты на <b><?= number_format($need, 2) ?> <?= GLOBAL_CURRENCY ?></b>.
		<input type="hidden" class="form-group_control" value="<?= $need ?>" name="<?= $rechargeModel->isComplete() ? 'OrderAmount' : 'RechargeBalanceForm[sum]' ?>">
	<? endif; ?>
</div>
<? if ($rechargeModel->isComplete()): ?>
	<input type="hidden" name="Merchant_ID" value="<?= ASSIST_MERCHANT_ID ?>">
	<input type="hidden" name="Signature" value="<?= $rechargeModel->getKey() ?>">
	<input type="hidden" name="OrderCurrency" value="<?= GLOBAL_CURRENCY ?>">
	<input type="hidden" name="CustomerNumber" value="<?= $rechargeModel->getPhone() ?>">
	<input type="hidden" name="URL_RETURN_OK" value="<?= EXTERNAL_URL . 'user/recharge-ok' ?>">
	<input type="hidden" name="URL_RETURN_NO" value="<?= EXTERNAL_URL . 'user/recharge-no' ?>">
	<input type="hidden" name="OrderNumber" value="<?= $rechargeModel->order->order_number ?>">
	<input type="hidden" name="MobilePhone" value="<?= $rechargeModel->getPhone() ?>">
	<? if (YII_ENV == 'prod'): ?>
		<input type="hidden" name="account" value="<?= $rechargeModel->getPhone() ?>">
		<input type="hidden" name="CardPayment" value="1">
		<input type="hidden" name="MobiconPayment" value="0">
	<? endif; ?>
<? endif; ?>
<? if (isset($subscriptionInfo) && $subscriptionInfo->isAgreementRequired() && !$rechargeModel->isComplete()): ?>
<div class="<?= $this->context->isMobile ? ' -mobile-padding' : '' ?>">
	<h5 class="subscription_rules -visible -mt20" data-error="Для проведения платежа необходимо подтвердить согласие на подключение услуги «МТС Деньги»">
		<div class="subscription_rules_text">
			<div class="rules_agree-box_wrap<?= $this->context->isMobile ? '' : ' -middle' ?>"><?= Html::mtsCheckbox('agree', false, ['class' => 'rules_agree-box']) ?></div>
			Нажимая «Выполнить», Вы подтверждаете, что ознакомлены и согласны с <a href="/help/user-agreement" target="_blank">Правилами&nbsp;системы</a> и подключением услуги «МТС&nbsp;Деньги». <span class="rules_agree-info"><?= $subscriptionInfo->getInfo() ?></span>
		</div>
	</h5>
</div>
<? endif; ?>
<div class="pay-invoices-mobile_button-content<?= $this->context->isMobile ? ' -mobile-padding' : '' ?>">
	<?= Html::mtsButton('Выполнить', ['class' => '--submit-button', 'disabled' => $rechargeModel->isComplete()]) ?>
</div>
</form>