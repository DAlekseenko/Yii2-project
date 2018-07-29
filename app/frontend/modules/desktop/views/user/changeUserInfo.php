<?php
/* @var $this yii\web\View */
/* @var $form common\components\widgets\ActiveForm */
/* @var $model \frontend\models\virtual\ChangeUserInfo */
/* @var $showHint bool */
use common\components\widgets\ActiveForm;
use common\helpers\Html;
?>
<div class="change-user-info">
	<h3 class="header-small">Изменить данные пользователя</h3>

	<?php $form = ActiveForm::begin(['id' => 'changeUserInfoForm', 'fieldConfig' => ['options' => ['class' => 'form-group -form-simple']], 'action' => ['user/change-user-info']]); ?>

	<?= $form->field($model, 'last_name') ?>

	<?= $form->field($model, 'first_name') ?>

	<?= $form->field($model, 'patronymic') ?>

	<div class="-align_right">
		<?=Html::mtsButton('Сохранить')?>
	</div>
	<?php ActiveForm::end(); ?>
</div>
