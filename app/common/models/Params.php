<?php

namespace common\models;

use phpDocumentor\Reflection\DocBlock\Tags\Param;

/**
 * @property int $id
 * @property string $type_id
 * @property array $data
 * @property string $entity_type
 * @property int $entity_id
 * @property string title
 * @property string name
 */
class Params extends AbstractModel implements \JsonSerializable
{
    const BOOL_PARAM = 'bool';
    const NUMBER_PARAM = 'number';
    const STRING_PARAM = 'string';
    const ARRAY_PARAM = 'array';
    const ASSOC_PARAM = 'assoc';
    const LIST_PARAM = 'list';

    public static function findByName($name)
    {
        return self::find()->where(['name' => $name])->one();
    }

    public function getValue()
    {
        switch ($this->type_id) {
            case self::BOOL_PARAM:
                return (bool)(int)$this->data['value'];
            default:
                return $this->data['value'];
        }
    }

    public function getValueForFront()
    {
        switch ($this->type_id) {
            case self::BOOL_PARAM:
                return [self::BOOL_PARAM => (int)$this->data['value']];
            case self::NUMBER_PARAM:
                return [self::NUMBER_PARAM => $this->data['value']];
            case self::ARRAY_PARAM:
                return [self::ARRAY_PARAM => $this->data['value']];
            case self::ASSOC_PARAM:
                return [self::ASSOC_PARAM => $this->data['value']];
            case self::STRING_PARAM:
                return [self::STRING_PARAM =>
                    [
                        'value' => $this->data['value'],
                        'insertions' => $this->issetValue($this->data, 'insertions')
                    ]
                ];
            case self::LIST_PARAM:
                return [self::LIST_PARAM =>
                    [
                        'value' => $this->data['value'],
                        'selections' => $this->issetValue($this->data, 'selections')
                    ]
                ];
            default:
                return $this->data['value'];
        }
    }

    public function setValue($data)
    {
        switch ($this->type_id) {
            case self::BOOL_PARAM:
                $this->data = ['value' => (int)$data[self::BOOL_PARAM]];
                break;
            case self::NUMBER_PARAM:
                $this->data = ['value' => $this->issetValue($data, self::NUMBER_PARAM)];
                break;
            case self::STRING_PARAM:
                $this->data = [
                    'value' => $this->issetValue($data, self::STRING_PARAM),
                    'insertions' => $this->issetValue($this->data, 'insertions')
                ];
                break;
            case self::ARRAY_PARAM:
                $this->data = ['value' => $this->issetValue($data, self::ARRAY_PARAM)];
                break;
            case self::ASSOC_PARAM:
                $this->data = ['value' => $this->issetValue($data, self::ASSOC_PARAM)];
                break;
            case self::LIST_PARAM:
                $this->data = [
                    'value' => $this->issetValue($data, self::LIST_PARAM),
                    'selections' => $this->issetValue($this->data, 'selections')
                ];
                break;
            default:
                break;
        }
        return true;
    }

    public function issetValue($data, $param)
    {
        return isset($data[$param]) ? $data[$param] : '';
    }

    public function jsonSerialize()
    {
        return array_merge($this->getAttributes(), $this->getValueForFront());
    }
}
