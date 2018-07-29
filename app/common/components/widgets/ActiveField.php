<?php
namespace common\components\widgets;
use common\helpers\Html;

class ActiveField extends \yii\widgets\ActiveField {
	public $options = ['class' => 'form-group'];
	public $errorOptions = ['class' => 'form-group_help'];
	public $labelOptions = ['class' => 'form-group_label'];
	public $inputOptions = ['class' => 'form-group_control'];
	public $hintOptions = ['class' => 'form-group_hint'];

	public function mtsCheckbox($options = [], $enclosedByLabel = true)
	{
		if ($enclosedByLabel) {
			$this->parts['{input}'] = Html::activeMtsCheckbox($this->model, $this->attribute, $options);
			$this->parts['{label}'] = '';
		} else {
			if (isset($options['label']) && !isset($this->parts['{label}'])) {
				$this->parts['{label}'] = $options['label'];
				if (!empty($options['labelOptions'])) {
					$this->labelOptions = $options['labelOptions'];
				}
			}
			unset($options['labelOptions']);
			$options['label'] = null;
			$this->parts['{input}'] = Html::activeMtsCheckbox($this->model, $this->attribute, $options);
		}
		$this->adjustLabelFor($options);

		return $this;
	}
}
