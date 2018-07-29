<?php

namespace common\models\virtual;

class CaptchaRegistration extends Registration
{
	public $verifyCode;

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		$rules = parent::rules();
		$rules[] = ['verifyCode', 'captcha', 'captchaAction' => 'common/site/captcha'];
		return $rules;
	}
}
