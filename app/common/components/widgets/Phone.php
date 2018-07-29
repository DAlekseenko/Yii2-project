<?php
namespace common\components\widgets;

use common\helpers\Html;
use yii;
use common\components\widgets\traits\CommonWidget;

//рисует красивое поле ввода телефона. Используется, например, при авторизации
class Phone extends yii\widgets\InputWidget
{
	use CommonWidget;

	public $inputOptions = [];
	public $codeOptions = [];

	public function init()
	{
		parent::init();
		if (!isset($this->inputOptions['placeholder'])) {
			$this->inputOptions['placeholder'] = '** - *** - ****';
		}
		$this->inputOptions['id'] = yii\helpers\ArrayHelper::remove($this->options, 'id');
		$this->prependStringValue($this->options, 'mts-phone-field');
		$this->prependStringValue($this->codeOptions, 'mts-phone-field_code');
		$this->prependStringValue($this->inputOptions, 'mts-phone-field_value --phone-value-mask');
	}

	public function run()
	{
		return $this->renderViewFile('phone', [
			'model' => $this->model,
			'attribute' => $this->attribute,
			'name' => $this->name ?: Html::getInputName($this->model, $this->attribute),
			'value' => $this->value ?: Html::getAttributeValue($this->model, $this->attribute),
			'options' => $this->options,
			'codeOptions' => $this->codeOptions,
			'inputOptions' => $this->inputOptions,
		]);
	}
}