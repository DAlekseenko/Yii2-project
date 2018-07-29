<?php
/* @var $this yii\web\View */
/* @var $form common\components\widgets\ActiveForm */
/* @var $changeUserInfoModel frontend\models\virtual\ChangeUserInfo */
/* @var $showHint bool */
use common\components\widgets\ActiveForm;
use common\helpers\Html;
?>
<div>
	<?php $form = ActiveForm::begin(['id' => 'settingsChangeUserInfoForm', 'fieldConfig' => ['options' => ['class' => 'form-group -settings-form']], 'action' => ['settings/change-user-info']]); ?>

	<?= $form->field($changeUserInfoModel, 'last_name', ['inputOptions' => ['id' => 'settingsLastName', 'class' => 'form-group_control']]) ?>

	<?= $form->field($changeUserInfoModel, 'first_name', ['inputOptions' => ['id' => 'settingsFirstName', 'class' => 'form-group_control']]) ?>

	<?= $form->field($changeUserInfoModel, 'patronymic', ['inputOptions' => ['id' => 'settingsPatronymic', 'class' => 'form-group_control']]) ?>

	<?= Html::mtsButton('Сохранить', ['class' => '-settings-form'])?>
	<?php ActiveForm::end(); ?>
</div>
