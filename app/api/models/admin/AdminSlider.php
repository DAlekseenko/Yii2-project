<?php

namespace api\models\admin;

use common\models\Slider;


class AdminSlider extends Slider implements \JsonSerializable
{
    public $uploadImage;

    public function load($data, $formName = null)
    {
        $load = parent::load($data, $formName);
        $this->banner = !empty($data['banner']) ? true : false;
        $this->uploadImage = $data['banner'];
        return $load;
    }

    public function afterSave($insert, $changedAttributes)
    {
        $this->setFileFromBase64();
        parent::afterSave($insert, $changedAttributes);
    }

    public function jsonSerialize()
    {
        return array_merge($this->getAttributes(), [
            'banner' => $this->getBase64file()
        ]);
    }
}