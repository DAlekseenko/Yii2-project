<?php

namespace api\models\admin;

use common\models\Recommendations;


class AdminRecommendations extends Recommendations implements \JsonSerializable
{
    public $uploadImage;

    public function load($data, $formName = null)
    {
        $load = parent::load($data, $formName);
        $this->icon = !empty($data['icon']) ? true : false;
        $this->uploadImage = $data['icon'];
        return $load;
    }

    public function afterSave($insert, $changedAttributes)
    {
        $this->setFileFromBase64();
        parent::afterSave($insert, $changedAttributes);
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'type' => $this->isService() ? 'Услуга' : 'Категория',
            'name' => $this->entity ? $this->entity->name : '',
            'key' => $this->entity ? $this->entity->Ukey : '',
            'icon' => $this->getBase64file()
        ];
    }
}