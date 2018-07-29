<?php
/**
 * @var $id string
 * @var $sendMailForm \frontend\models\virtual\SendMailForm
 */
use common\components\widgets\ActiveForm;

$dataId = (empty($id) ? '{id}' : $id);
$form = ActiveForm::begin(
	[
		'id'      => 'sendInvoiceForm',
		'options' => ['class' => 'form -one-field-form -send-result'],
		'action'  => ['/api/payments/send-invoice?key=' . $dataId],
	]);
?>
<?= $form->field($sendMailForm, 'email', ['template' => '{input}{error}'])->textInput(
	[
		'value'       => $sendMailForm->email,
		'placeholder' => 'введите e-mail',
	]);
?>
<input class="mts-button" type="submit" value="Отправить квитанцию">
<?
ActiveForm::end();
?>



