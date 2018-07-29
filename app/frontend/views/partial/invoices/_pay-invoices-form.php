<?php
/**
 * @var \api\components\services\Subscription\Entities\UserSubscriptionInfo $subscriptionInfo
 */

use common\helpers\Html;
?>
<form class="pay-invoices-mobile" method="post">
	<?= Html::hiddenInput('_csrf', Yii::$app->request->csrfToken)?>
	<?= Html::hiddenInput('actionPay', 1)?>
	<? if (isset($subscriptionInfo) && $subscriptionInfo->isAgreementRequired()): ?>
		<div class="<?= $this->context->isMobile ? ' -mobile-padding' : '' ?>">
			<h5 class="subscription_rules -visible -mt20" data-error="Для проведения платежа необходимо подтвердить согласие на подключение услуги «МТС Деньги»">
				<div class="subscription_rules_text">
					<div class="rules_agree-box_wrap<?= $this->context->isMobile ? '' : ' -middle' ?>"><?= Html::mtsCheckbox('agree', false, ['class' => 'rules_agree-box']) ?></div>
					Нажимая «Оплатить», Вы подтверждаете, что ознакомлены и согласны с <a href="/help/user-agreement" target="_blank">Правилами&nbsp;системы</a> и подключением услуги «МТС&nbsp;Деньги». <span class="rules_agree-info"><?= $subscriptionInfo->getInfo() ?></span>
				</div>
			</h5>
		</div>
	<? endif; ?>
	<div class="pay-invoices-mobile_button-content<?= $this->context->isMobile ? ' -mobile-padding' : '' ?>">
		<?= Html::mtsButton('Оплатить') ?>
	</div>
</form>
