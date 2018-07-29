<?php

/* @var $this yii\web\View */
/* @var $form common\components\widgets\ActiveForm */
/* @var $loginFormModel \common\models\virtual\Login */
/* @var $isNew bool */
use common\helpers\Html;
use common\components\widgets\ActiveForm;
?>

<?php $form = ActiveForm::begin(['enableClientScript' => false, 'id' => 'sendPasswordSuccessForm', 'action' => ['site/send-password-success?new=' . (int) $isNew], 'fieldConfig' => ['options' => ['class' => 'form-group -login']]]); ?>

<?= $form->field($loginFormModel, 'phone')->widget(\common\components\widgets\Phone::className()) ?>

<h5 class="-pb3">Вам было выслано SMS с кодом авторизации. Внимание! Не передавайте данный код третьим лицам!</h5>

<?= $form->field($loginFormModel, 'password')->passwordInput(['class' => 'form-group_control -password']) ?>

<h3 class="form-group">
	<?= Html::mtsButton($isNew ? 'Зарегистрироваться' : 'Войти', ['class' => '-max-width'])?>
</h3>

<?php ActiveForm::end(); ?>

<h3 class="aside-action-link">
	<a class="--link-update" data-url="/site/send-password?new=<?= (int) $isNew ?>">Выслать код повторно</a>
</h3>

<hr class="mts-hr">

<?=$this->render('//partial/site/userAgreementText', ['buttonName' => 'Зарегистрироваться'])?>
