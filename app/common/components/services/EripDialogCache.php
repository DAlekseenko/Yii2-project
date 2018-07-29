<?php

namespace common\components\services;

use yii;

class EripDialogCache
{
	/** @var \common\components\caching\MemCache|yii\caching\Cache */
	protected $cache;

	protected $data = null;

	protected $properties = null;

	protected $fieldsIndex = null;

	protected $id;

	public function __construct($id)
	{
		$this->id = $id;
		$this->cache = Yii::$app->cache;
	}

	public function load()
	{
		list($this->data, $this->properties) = $this->cache->get($this->id) ?: [[], []];
	}

	/**
	 * @return array
	 */
	public function getFields()
	{
		if ($this->data === null) {
			$this->load();
			foreach ($this->data as $item) {
				$this->fieldsIndex[ $item['name'] ] = true;
			}
		}
		return $this->data;
	}

	public function set(array $value)
	{
		$this->cache->set($this->id, $value);
	}

	public function setFieldsValue(array $fieldsValue)
	{
		$eripFields = $this->getFields();
		foreach ($eripFields as $key => $eripField) {
			if (isset($fieldsValue[ $eripField['name'] ])) {
				$eripFields[$key]['value'] = $fieldsValue[ $eripField['name'] ];
			}
		}
		$this->data = $eripFields;
	}

	public function appendFields(array $newFields)
	{
		$eripFields = $this->getFields();
		foreach ($newFields as $newField) {
			if ($newField['editable'] == true && !isset($this->fieldsIndex[ $newField['name'] ])) {
				$this->fieldsIndex[ $newField['name'] ] = true;
				$eripFields[] = $newField;
			}
		}
		$this->data = $eripFields;
	}

	public function __destruct()
	{
		if ($this->data !== null || !empty($this->properties)) {
			$this->set([$this->data, $this->properties]);
		}
	}
}
