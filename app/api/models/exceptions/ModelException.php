<?php

namespace api\models\exceptions;

use yii\base\Model;

class ModelException extends \Exception
{
	protected $model;

	public function __construct(Model $model, $text = null)
	{
		$this->model = $model;
		if (isset($text)) {
			$errors = $model->getFirstErrors();
			reset($errors);
			$first_key = key($errors);
			$model->clearErrors($first_key);
			$model->addError($first_key, $text);
		}
		$errors = $model->getFirstErrors();
		parent::__construct(reset($errors));
	}

	/**
	 * @return Model
	 */
	public function getModel()
	{
		return $this->model;
	}
}