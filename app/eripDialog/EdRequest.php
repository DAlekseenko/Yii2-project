<?php

namespace eripDialog;

use yii\base\Component;
use eripDialog\EdHelper as H;

/**
 * Class EdRequest
 * @package eripDialog
 */
class EdRequest extends Component
{
	protected $data = [H::F_MODE => H::MODE_START];

	public function set(array $data)
	{
		$this->data = $data;
		if (!isset($this->data[H::F_MODE])) {
			$this->data[H::F_MODE] = H::MODE_START;
		}

		return $this;
	}

	public function get()
	{
		return $this->data;
	}

	public function getSession()
	{
		return $this->hasSession() ? $this->data[H::F_MTS_MONEY_SESSION] : '';
	}

	public function getTransaction()
	{
		return isset($this->data[H::F_TRANSACTION]) ? $this->data[H::F_TRANSACTION] : null;
	}

	public function setSession($session)
	{
		$this->data[H::F_MTS_MONEY_SESSION] = $session;

		return $this;
	}

	public function hasSession()
	{
		return isset($this->data[H::F_MTS_MONEY_SESSION]) && preg_match('/^\w{8}-\w{4}-\w{4}-\w{4}-\w{12}$/i', $this->data[H::F_MTS_MONEY_SESSION]);
	}

	public function mergeWithCache(EdCache $cache)
	{
		$this->data = array_merge($this->data, $cache->getProperties(), [H::F_MTS_MONEY_SESSION => $cache->getId()]);
	}

	public function isStart()
	{
		return empty($this->hasSession());
	}

	public function getSum()
	{
		return isset($this->data[H::F_SUM]) ? $this->data[H::F_SUM]: null;
	}

	public function isPay()
	{
		return isset($this->data[H::F_SERVICE_CODE], $this->data[H::F_SUM]);
	}

	public function getMethodName()
	{
		return isset($this->data[H::F_MODE]) ? $this->data[H::F_MODE] : null;
	}

	public function getServiceCode()
	{
		return isset($this->data[H::F_SERVICE_CODE]) ? $this->data[H::F_SERVICE_CODE] : null;
	}
}
