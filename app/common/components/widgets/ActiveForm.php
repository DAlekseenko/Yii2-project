<?php
namespace common\components\widgets;

class ActiveForm extends \yii\widgets\ActiveForm {
	public $requiredCssClass = '-required';
	public $errorCssClass = '-has-error';
	public $successCssClass = '-has-success';
	public $fieldClass = 'common\components\widgets\ActiveField';
	public $validateOnChange = false;
	public $validateOnBlur = false;
}