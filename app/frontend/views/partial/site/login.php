<?php

/* @var $this yii\web\View */
/* @var $form common\components\widgets\ActiveForm */
/* @var $loginFormModel \common\models\virtual\Login */
use common\helpers\Html;
use common\components\widgets\ActiveForm;
?>
<?php $form = ActiveForm::begin(['id' => 'loginForm', 'action' => ['site/login'], 'fieldConfig' => ['options' => ['class' => 'form-group -login']]]); ?>
    <?= $form->field($loginFormModel, 'phone')->widget(\common\components\widgets\Phone::className()) ?>
    <?= $form->field($loginFormModel, 'password')->passwordInput(['class' => 'form-group_control -password']) ?>
    <h3 class="form-group">
        <?= Html::mtsButton('Войти', ['class' => '-max-width'])?>
    </h3>
    <h3 class="aside-action-link">
        <a class="--link-update --sign-link" data-url="/site/send-password">Зарегистрироваться</a>
    </h3>
    <h5 class="aside-action-link -bold_none">
        <a class="--link-update" data-url="/site/send-password?new=0">Войти&nbsp;с&nbsp;помощью разового&nbsp;пароля</a>
    </h5>
    <hr class="mts-hr">
    <?=$this->render('//partial/site/userAgreementText', ['buttonName' => 'Войти'])?>
<?php ActiveForm::end(); ?>
