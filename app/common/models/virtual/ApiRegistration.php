<?php

namespace common\models\virtual;

class ApiRegistration extends Registration
{
	public function load($data, $formName = null)
	{
		return parent::load($data, '');
	}
}