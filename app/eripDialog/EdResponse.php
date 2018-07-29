<?php

namespace eripDialog;

use eripDialog\EdHelper as H;
use yii\web\HttpException;

class EdResponse
{
	const STATUS_FAIL = 'fail';

	protected $response = null;

	public function __construct($eripResponse = null)
	{
		if ($eripResponse !== null) {
			$this->setResponse($eripResponse);
		}
	}

	public function clear()
	{
		$this->response = null;
	}

	/** Подсчитывает кол-во редактируемых полей в ответе ERIP */
	public function countEditableFields()
	{
		$counter = 0;
		if (isset($this->response[H::F_FIELDS])) {
			foreach ($this->response[H::F_FIELDS] as $item) {
				$counter += (int)(bool)$item['editable'];
			}
		}
		return $counter;
	}

	public function getEditableFields()
	{
		$result = [];
		if (isset($this->response[H::F_FIELDS])) {
			foreach ($this->response[H::F_FIELDS] as $item) {
				if ($item['editable'] !== true) {
					continue;
				}
				$result[$item['name']] = isset($item['value']) ? $item['value'] : '';
			}
		}
		return $result;
	}

	/**
	 * @param  string|array $eripResponse
	 * @return $this
	 * @throws HttpException
	 */
	public function setResponse($eripResponse)
	{
		if (is_array($eripResponse)) {
			$this->response = $eripResponse;
		} else {
			$this->response = json_decode($eripResponse, 1);
		}

		if (empty($this->response)) {
			throw new HttpException(204);
		}
		return $this;
	}

	public function setError($text)
	{
		$this->response = [
			H::F_STATUS => self::STATUS_FAIL,
			H::F_ERRORS => $text
		];
		return $this;
	}

	public function setFields(array $fields)
	{
		$this->response[H::F_FIELDS] = $fields;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getFields()
	{
		return isset($this->response[H::F_FIELDS]) ? $this->response[H::F_FIELDS] : [];
	}

	public function addField($field)
	{
		if (!isset($this->response[H::F_FIELDS])) {
			$this->response[H::F_FIELDS] = [];
		}
		$this->response[H::F_FIELDS][] = $field;

		return $this;
	}

	public function get()
	{
		return $this->response;
	}

	public function getSummary()
	{
		return $this->hasSummary() ? $this->response[H::F_SUMMARY] : null;
	}

	public function getSum()
	{
		return $this->isSum() ? $this->response[H::F_SUM] : [];
	}

	public function getDateCreate()
	{
		return isset($this->response[H::F_DATE]) ? $this->response[H::F_DATE] : null;
	}

	public function getCommission()
	{
		return $this->hasCommission() ? $this->response[H::F_COMMISSION] : 0;
	}

	public function getPaymentId()
	{
		return $this->hasPaymentId() ? $this->response[H::F_PAYMENT_ID] : 0;
	}

	public function getPaymentInfo()
	{
		return [
			H::F_PAYMENT_ID => $this->hasPaymentId() ? $this->response[H::F_PAYMENT_ID] : 0,
			H::F_SERVER_NAME => isset($this->response[H::F_SERVER_NAME]) ? $this->response[H::F_SERVER_NAME] : '',
			H::F_SERVER_TIME => isset($this->response[H::F_SERVER_TIME]) ? $this->response[H::F_SERVER_TIME] : '',
			H::F_SENDER => isset($this->response[H::F_SENDER]) ? $this->response[H::F_SENDER] : '',
			H::F_RECEIVER => isset($this->response[H::F_RECEIVER]) ? $this->response[H::F_RECEIVER] : '',
		];
	}

	public function hasErrors()
	{
		return isset($this->response[H::F_ERRORS]);
	}

	public function getError()
	{
		return $this->hasErrors() ? $this->response[H::F_ERRORS] : null;
	}

	public function hasCommission()
	{
		return isset($this->response[H::F_COMMISSION]);
	}

	public function hasPaymentId()
	{
		return isset($this->response[H::F_PAYMENT_ID]);
	}

	public function hasSummary()
	{
		return isset($this->response[H::F_SUMMARY]);
	}

	public function isSum()
	{
		return isset($this->response[H::F_SUM]);
	}

	public function getMode()
	{
		return isset($this->response[H::F_MODE]) ? $this->response[H::F_MODE] : null;
	}

	public function getSid()
	{
		return isset($this->response[H::F_SID]) ? $this->response[H::F_SID] : null;
	}
}
