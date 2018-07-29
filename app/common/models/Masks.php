<?php

namespace common\models;

/**
 * @property string $name
 * @property string $prefix
 * @property string $mask
 * @property string $postfix
 */
class Masks extends AbstractModel implements \JsonSerializable
{
    public function jsonSerialize()
    {
        return $this->getAttributes();
    }

    public static function tableName()
    {
        return 'masks';
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'unique'],
            [['name', 'prefix', 'postfix'], 'string', 'max' => 30],
            ['name', 'match', 'pattern' => '/^[a-z_-]+$/i'],
            [['mask'], 'string', 'max' => 60],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Название',
            'prefix' => 'Prefix',
            'mask' => 'Маска',
            'postfix' => 'Postfix',
        ];
    }
}
