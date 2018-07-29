<?php

namespace common\components\lib;

use Detection\MobileDetect;
use yii;

class Device extends yii\base\Component
{
    /**
     * @var string|null
     */
    public $headers = null;

    /**
     * @var string|null
     */
    public $userAgent = null;

    /**
     * @var MobileDetect
     */
    public $mobileDetect;

    /**
     * @var string
     */
    protected $iosStoreLink = '';

    /**
     * @var string
     */
    protected $androidStoreLink = '';

	public function init()
    {
        $this->mobileDetect = new MobileDetect();
        if(null !== $this->headers) {
            $this->mobileDetect->setHttpHeaders($this->headers);
        }
        if(null !== $this->userAgent) {
            $this->mobileDetect->setUserAgent($this->userAgent);
        }

        $this->iosStoreLink = yii::$app->params['store']['os'];
        $this->androidStoreLink = yii::$app->params['store']['android'];
    }

	public function isAndroid()
	{
		return $this->mobileDetect->version('Android') || (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/Android/i', $_SERVER['HTTP_USER_AGENT']));
	}

	public function isIos()
	{
		return $this->mobileDetect->isIOS();
	}

	public function iosStoreLink()
	{
		return $this->iosStoreLink;
	}

	public function androidStoreLink()
	{
		return $this->androidStoreLink;
	}

}