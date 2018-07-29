<?php

namespace common\components\services;

use yii\base\Model;
use yii\validators\EmailValidator;
use yii\validators\NumberValidator;
use yii\validators\RequiredValidator;
use yii\validators\StringValidator;

class ModelManager
{
	protected $model = null;

	public function __construct(Model $model)
	{
		$this->model = $model;
	}

	public function getFieldProperties()
	{
		$fields = $this->model->attributeLabels();

		foreach ($fields as $key => $value) {
			$fields[$key] = ['label' => $value, 'required' => false, 'min' => null, 'max' => null, 'type' => 'string', 'max_length' => null, 'min_length' => null];
		}
		foreach ($this->model->getValidators() as $validator) {
			if ($validator instanceof RequiredValidator) {
				foreach ($validator->attributes as $attr) {
					$fields[$attr]['required'] = isset($fields[$attr]);
				}
			}
			if ($validator instanceof StringValidator) {
				foreach ($validator->attributes as $attr) {
					if (isset($fields[$attr])) {
						$fields[$attr]['type'] = 'string';
						$fields[$attr]['max_length'] = $validator->max;
						$fields[$attr]['min_length'] = $validator->min;
					}
				}
			}
			if ($validator instanceof NumberValidator) {
				foreach ($validator->attributes as $attr) {
					if (isset($fields[$attr])) {
						$fields[$attr]['type'] = $validator->integerOnly ? 'integer' : 'number';
						$fields[$attr]['max'] = $validator->max;
						$fields[$attr]['min'] = $validator->min;
					}
				}
			}
			if ($validator instanceof EmailValidator) {
				foreach ($validator->attributes as $attr) {
					if (isset($fields[$attr])) {
						$fields[$attr]['type'] = 'email';
					}
				}
			}
		}

		return $fields;
	}
}
