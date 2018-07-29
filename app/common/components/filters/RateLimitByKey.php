<?php
namespace common\components\filters;

use yii;
use yii\base\Object;
use yii\filters\RateLimitInterface;

class RateLimitByKey extends Object implements RateLimitInterface
{
	public $window = 15 * 60;//15 минут
	/** @todo поставил 999 по просьбе мобильных разработчиков, убрать в дальнейшем */
	public $limit = 999;//3 раза
	public $key;

	public function getRateLimit($request, $action)
	{
		return [$this->limit, $this->window];
	}

	public function loadAllowance($request, $action)
	{
		return Yii::$app->cache->get($this->getKey($request, $action)) ?: [$this->limit, 0];
	}

	public function saveAllowance($request, $action, $allowance, $timestamp)
	{
		Yii::$app->cache->set($this->getKey($request, $action), [$allowance, $timestamp], $this->window);
	}

	private function getKey($request, $action)
	{
		return 'rateLimitCache' . $action->controller->id . $action->id . $this->key;
	}
}