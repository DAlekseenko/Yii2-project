<?php

namespace eripDialog\stepHandlers;

use eripDialog\EdHelper as H;

class FieldsHandler extends AbstractEripCaller
{
	protected function getNextMode()
	{
		return $this->response->isSum() ? H::MODE_PAY : H::MODE_FIELDS;
	}
}
