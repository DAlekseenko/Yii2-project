<?php
/**
 * @var $addFavorite \frontend\models\virtual\AddFavorite
 */
use common\components\widgets\ActiveForm;

if (empty($addFavorite->id)) {
	$formSettings = [
		'id'      => 'addFavoriteForm',
		'options' => ['class' => 'form -one-field-form -inside-popup'],
		'action'  => '/api/payments/add-favorite?key=' . (empty($id) ? '{id}' : $id),
	];
} else {
	$formSettings = [
		'id'      => 'addFavoriteForm',
		'options' => ['class' => 'form -one-field-form'],
		'action'  => '/api/payments/update-favorite?id=' . $addFavorite->id,
	];
}

$form = ActiveForm::begin($formSettings);
?>
<?= $form->field($addFavorite, 'name', ['template' => '{input}{error}'])->textInput(
	[
		'value'       => $addFavorite->name ?: '{name}',
		'placeholder' => 'название',
	]);
?>
<input class="mts-button" type="submit" value="Сохранить">
<?
ActiveForm::end();

?>
