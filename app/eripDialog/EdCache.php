<?php

namespace eripDialog;

use yii;

class EdCache extends yii\base\Component
{
	/** @var \common\components\caching\MemCache|yii\caching\Cache */
	protected $cache;

	protected $data = null;

	protected $properties = null;

	protected $fieldsIndex = null;

	protected $id;

	public function init()
	{
		parent::init();
		$this->cache = Yii::$app->cache;
	}

	public function setId($id)
	{
		$this->id = $id;
	}

	public function getId()
	{
		return $this->id;
	}

	public function load()
	{
		if (!isset($this->id)) {
			throw new \Exception('Dialog session id is not defined');
		}
		if (!(isset($this->data) || isset($this->properties))) {
			list($this->data, $this->properties) = $this->cache->get($this->id) ?: [[], []];
		}
	}

	public function validate()
	{
		$this->load();
		return !(empty($this->data) && empty($this->properties));
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
		$this->cache->set($this->id, $value, 60*30);
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

	public function getProperties()
	{
		if (!isset($this->properties)) {
			$this->load();
		}
		return $this->properties;
	}

	public function setProperties(array $properties)
	{
		$this->properties = $properties;
	}

	public function appendProperty($name, $value)
	{
		$this->properties[$name] = $value;

		return $this;
	}

	public function clear()
	{
		$this->data = $this->properties = null;
		$this->cache->delete($this->id);
	}

	public function __destruct()
	{
		if ($this->data !== null || $this->properties !== null) {
			$this->set([$this->data, $this->properties]);
		}
	}
}
