<?php
/** @var $model \frontend\models\virtual\RechargeBalanceForm */
use common\components\widgets\ActiveForm;
use common\helpers\Html;

$form = ActiveForm::begin(['action' => $model->getAction(), 'options' => ['class' => 'recharge-form'], 'id' => $model->isComplete() ? 'bankForm' : 'rechargeForm', 'fieldConfig' => ['options' => ['class' => 'form-group -settings-form' . ($this->context->isMobile ? '' : ' -without-mb')]]]);

if ($model->isComplete()): ?>
	<input type="hidden" name="Merchant_ID" value="<?= ASSIST_MERCHANT_ID ?>">
	<input type="hidden" name="Signature" value="<?= $model->getKey() ?>">
	<input type="hidden" name="OrderCurrency" value="<?= GLOBAL_CURRENCY ?>">
	<input type="hidden" name="CustomerNumber" value="<?= $model->getPhone() ?>">
	<input type="hidden" name="URL_RETURN_OK" value="<?= EXTERNAL_URL . 'user/recharge-ok'?>">
	<input type="hidden" name="URL_RETURN_NO" value="<?= EXTERNAL_URL . 'user/recharge-no'?>">
	<input type="hidden" name="OrderNumber" value="<?= $model->order->order_number ?>">
	<input type="hidden" name="MobilePhone" value="<?= $model->getPhone() ?>">
	<? if (YII_ENV == 'prod'): ?>
		<input type="hidden" name="account" value="<?= $model->getPhone() ?>">
		<input type="hidden" name="CardPayment" value="1">
		<input type="hidden" name="MobiconPayment" value="0">
	<? endif; ?>
<? endif; ?>
<div class="form-group -settings-form -without-mb">
	<label class="form-group_label" for="phone">Номер телефона</label>
	<input type="text" id="phone" class="form-group_control" value="<?= $model->getPhone() ?>" disabled>
</div>
<div class="form-group -settings-form -without-mb -sum -required <?= $model->getFirstError('sum') ? '-has-error' : '' ?>">
	<label class="form-group_label" for="sum"><?= $model->getAttributeLabel('sum') ?></label>
	<div class="form-group_complex-control">
		<input class="form-group_control -half" type="text" id="rub" placeholder="рублей" value="<?= $model->getRub() ?>" maxlength="3" autocomplete="off">
		<input class="form-group_control -half" type="text" id="kop" placeholder="копеек" value="<?= $model->getKop() ?>" maxlength="2" autocomplete="off">
	</div>
	<input type="hidden" id="sum" class="form-group_control" value="<?= $model->sum ?>" name="<?= $model->isComplete() ? 'OrderAmount' : 'RechargeBalanceForm[sum]' ?>">
	<div class="form-group_hint">Сумма&nbsp;платежа&nbsp;от&nbsp;1&nbsp;коп. до&nbsp;499&nbsp;руб.&nbsp;99&nbsp;коп.</div>
	<div class="form-group_help">
		<?= $model->getFirstError('sum') ?: '' ?>
	</div>
</div>

<h3>Данные плательщика:</h3>
<?= $form->field($model, 'first_name', ['inputOptions' => ['class' => 'form-group_control'] + ($model->isComplete() ? ['name' => 'Firstname'] : []) ]) ?>

<?= $form->field($model, 'last_name', ['inputOptions' => ['class' => 'form-group_control'] + ($model->isComplete() ? ['name' => 'Lastname'] : []) ]) ?>

<?= $form->field($model, 'email', ['inputOptions' => ['class' => 'form-group_control'] + ($model->isComplete() ? ['name' => 'Email'] : []) ]) ?>

<?= Html::mtsButton('Продолжить', ['class' => '-settings-form', 'disabled' => $model->isComplete()])?>
<?php ActiveForm::end(); ?>
