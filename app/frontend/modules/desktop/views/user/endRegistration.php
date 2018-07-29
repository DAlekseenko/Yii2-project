<?php
/**
 * @var $this yii\web\View
 * @var $context \yii\web\Controller|\frontend\modules\desktop\components\behaviors\RenderLayout
 * @var $model \frontend\models\virtual\EndRegistration
 */
use common\components\widgets\ActiveForm;
$this->title = 'МТС Деньги - Завершение регистрации';
$this->params['header'] = 'Завершение регистрации';

$context = $this->context;
$context->getBreadcrumbsLayout()->appendBreadcrumb('Завершение регистрации');
?>

<div class="end-registration">
	<?php $form = ActiveForm::begin(['fieldConfig' => ['options' => ['class' => 'form-group -end-registration']]]); ?>

	<?= $form->field($model, 'fio', ['options' => ['class' => 'form-group -end-registration -fio']])->textInput() ?>

	<?= $form->field($model, 'password')->passwordInput() ?>

	<?= $form->field($model, 'passwordRepeat')->passwordInput() ?>

	<?=\common\helpers\Html::mtsButton('Сохранить')?>

	<?php ActiveForm::end(); ?>
</div>

<?= \common\helpers\Html::a('Продолжить без изменений', ['/invoices'])?>
