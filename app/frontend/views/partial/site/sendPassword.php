<?php
/* @var $this yii\web\View */
/* @var $form common\components\widgets\ActiveForm */
/* @var $smsSenderModel \common\models\virtual\CaptchaRegistration */
/* @var $isNew bool */
use common\helpers\Html;
use common\components\widgets\ActiveForm;
use common\components\widgets\Captcha;
?>
<?php $form = ActiveForm::begin(['enableClientScript' => false, 'id' => 'sendPasswordForm', 'fieldConfig' => ['options' => ['class' => 'form-group -login']], 'action' => ['site/send-password?new=' . (int) $isNew]]); ?>

<?= $form->field($smsSenderModel, 'phone')->widget(\common\components\widgets\Phone::className()) ?>

<h5 class="-pb3"><?= $isNew ? 'Для продолжения регистрации, пожалуйста, введите символы с картинки' : 'Введите, пожалуйста, символы с картинки' ?></h5>

<?php
$configCaptcha = [
	'template' => "<label for='" . Html::getInputId($smsSenderModel, 'verifyCode') . "' class='form-group_label -captcha label-vefifycode'>{image}</label>\n{input}",
];
$configField = ['options' => ['class' => 'form-group -login'], 'template' => "{input}\n{error}",];
?>
<?= $form->field($smsSenderModel, 'verifyCode', $configField)->widget(Captcha::className(), $configCaptcha) ?>

<h3 class="form-group">
	<?= Html::mtsButton('Продолжить', ['class' => '-max-width'])?>
</h3>
<?php ActiveForm::end(); ?>

<h3 class="aside-action-link">
	<a class="--link-update" data-url="/site/login">Назад</a>
</h3>
