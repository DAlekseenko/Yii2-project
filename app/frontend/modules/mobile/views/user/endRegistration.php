<?php
/**
 * @var $this yii\web\View
 * @var $context \yii\web\Controller|\frontend\modules\desktop\components\behaviors\RenderLayout
 * @var $model \frontend\models\virtual\EndRegistration
 */
use common\components\widgets\ActiveForm;
$this->title = 'МТС Деньги - Спасибо за регистрацию!';
$this->params['header'] = 'Спасибо за регистрацию!';
$this->context->getBreadcrumbsLayout()->appendBreadcrumb('Завершение регистрации');
?>

<div class="end-registration -mobile-padding">
	<?php $form = ActiveForm::begin(['fieldConfig' => ['options' => ['class' => 'form-group -form-mobile']]]); ?>

	<div class="end-registration_hint">Пожалуйста, внесите необходимые изменения</div>

	<?= $form->field($model, 'fio')->textInput() ?>

	<?= $form->field($model, 'password')->passwordInput() ?>

	<?= $form->field($model, 'passwordRepeat')->passwordInput() ?>

	<?=\common\helpers\Html::mtsButton('Сохранить')?>

	<?php ActiveForm::end(); ?>
	<div class="end-registration_continue-link">
		<?= \common\helpers\Html::a('Продолжить без изменений', ['/invoices'])?>
	</div>
</div>