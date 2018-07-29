<?php
/* @var $this yii\web\View */
/* @var $form common\components\widgets\ActiveForm */
/* @var $changePasswordModel frontend\models\virtual\SettingsChangePassword */
/* @var $showHint bool */
use common\components\widgets\ActiveForm;
use common\helpers\Html;
?>
<div>
	<?php $form = ActiveForm::begin(['id' => 'settingsChangePasswordForm', 'fieldConfig' => ['options' => ['class' => 'form-group -settings-form']], 'action' => ['settings/change-password']]); ?>

	<?= $form->field($changePasswordModel, 'password')->passwordInput() ?>

	<?= $form->field($changePasswordModel, 'passwordRepeat')->passwordInput() ?>

	<?= Html::mtsButton('Сохранить', ['class' => '-settings-form'])?>
	<?php ActiveForm::end(); ?>
</div>
