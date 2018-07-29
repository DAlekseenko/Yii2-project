<?php
namespace api\components\web;

class Response extends \yii\web\Response
{
	const API_FORMAT_DEFAULT = 'default';
	const API_FORMAT_AS_IS = 'as_is';

	public $responseMessage;
	public $responseType;
	public $apiFormat = self::API_FORMAT_DEFAULT;

	public function init()
	{
		parent::init();
        $this->headers->set('Access-Control-Allow-Origin', '*');
		self::$httpStatuses[499] = 'Fields Error';
	}

	//установить тип ответа. Если TYPE_AS_IS, от ответ не будет ложиться в result+name+status
	public function setApiResponseFormat($apiFormat)
	{
		$this->apiFormat = $apiFormat;
	}

	public function setResponseMessage($message)
	{
		$this->responseMessage = $message;
	}

	public function setResponseType($type)
	{
		$this->responseType = $type;
	}
}