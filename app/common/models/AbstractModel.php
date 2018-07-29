<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * Абстрактный класс для моделей, который добавляет функционал работы с данными в json-формате, если в докблоке модели
 * данные свойства имеют тип array.
 */
abstract class AbstractModel extends ActiveRecord
{
	/**
	 * У всех экземпляров модели будет одинаковый докблок. Поэтому, если уже был создан экземпляр класса модели ранее,
	 * то в $modelsArrayProperties мы храним список его array полей.
	 *
	 * @var array
	 */
	private static $modelsArrayProperties = [];

	/**
	 * Значения json полей в виде массива. Определен в том случае, если к атрибуту или его элементу было обращение.
	 *
	 * @var array
	 */
	private $arrayFields = [];

	private static $docBlockFactory;

	public function init()
	{
		$this->initArrayProperties();

		parent::init();
	}

	public function __wakeup()
	{
		$this->initArrayProperties();
	}

	private function initArrayProperties()
	{
		if (!isset(self::$modelsArrayProperties[$this->className()])) {

			$rc = new \ReflectionClass($this);
			$fieldsList = $this->getArrayProperties($rc);

			foreach (class_parents($this) ?: [] as $parentName) {
				if ($parentName == self::class) {
					break;
				}
				$rc = new \ReflectionClass($parentName);
				$fieldsList = array_merge($this->getArrayProperties($rc), $fieldsList);
			}
			self::$modelsArrayProperties[$this->className()] = empty($fieldsList) ? false : $fieldsList;
		}
	}

	/**
	 * @param \ReflectionClass $rc
	 * @return array
	 */
	private function getArrayProperties(\ReflectionClass $rc)
	{
		$docBlock = $rc->getDocComment();
		if ($docBlock === false) {
			return [];
		}

		if (!isset(self::$docBlockFactory)) {
			self::$docBlockFactory = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
		}
		$docBlock = self::$docBlockFactory->create($docBlock);
		$fieldsList = [];
		$properties = $docBlock->getTagsByName('property');
		foreach ($properties as $property) {
			if ($property->getType() instanceof \phpDocumentor\Reflection\Types\Array_) {
				$fieldsList[$property->getVariableName()] = true;
			}
		}
		return $fieldsList;
	}

	public function __get($name)
	{
		$parentFieldValue = parent::__get($name);

		/** если поле, к которому мы обращаемся определено, как array... */
		if (isset(self::$modelsArrayProperties[$this->className()][$name])) {
			/** ...и поле декодировано, то выводим его значение */
			if (isset($this->arrayFields[$name])) {
				return $this->arrayFields[$name];
			}
			/** если значение поля в модели NULL: */
			if ($parentFieldValue === null) {
				return null;
			}

			/** если нет, то декодируем из json: */
			$this->arrayFields[$name] = $this->decodeArray($parentFieldValue);
			/** для таких полей настоятельно рекомендуется использовать тип Postgres: json/jsonb, если нет
			 * или используется другая база, то json может быть некорректен или превышен лимит вложенности */
			return $this->arrayFields[$name];
		}

		return $parentFieldValue;
	}

	public function __set($name, $value)
	{
		/** если поле, к которому мы обращаемся определено, как array... */
		if (isset(self::$modelsArrayProperties[$this->className()][$name])) {
			if (is_string($value)) {
				$value = $this->decodeArray($value);
			}
			$this->arrayFields[$name] = $value;
			if ($value === null) {
				parent::__set($name, $value);
			}
		} else {
			parent::__set($name, $value);
		}
	}

	public function beforeSave($insert)
	{
		foreach ($this->arrayFields as $key => $value) {
			parent::__set($key, $this->encodeArray($value));
		}
		return parent::beforeSave($insert);
	}

	protected function encodeArray($value)
	{
		return isset($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : null;
	}

	protected function decodeArray($value)
	{
		return json_decode($value, 1);
	}
}
