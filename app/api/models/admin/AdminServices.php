<?php

namespace api\models\admin;

use common\models\Services;
use common\components\behaviors\ImgBehavior;
use common\models\ServicesInfo;

class AdminServices extends Services implements \JsonSerializable
{
    public $uploadImage, $infoModel;

    public function saveInfo($data)
    {
        $infoModel = $this->getInfoModel();
        if ($infoModel->load($data, '') && $infoModel->save()) {
            return $this->saveIcons($data);
        }
        return false;
    }

    public function saveIcons($data)
    {
        foreach (array_keys(ImgBehavior::$images) as $type) {
            $this->uploadImage = $data[$type . '_icon'];
            $this->setFileFromBase64($type);
        }
        return true;
    }

    public function getInfoModel()
    {
        if ($this->infoModel === null) {
            $this->infoModel = ServicesInfo::findOne($this->id) ?: new ServicesInfo(['service_id' => $this->id]);
        }
        return $this->infoModel;
    }

    public function getServicesInfo()
    {
        return $this->hasOne(ServicesInfo::className(), ['service_id' => 'id']);
    }

    public function jsonSerialize()
    {
        $categories = array_merge($this->getAttributes(), $this->getInfoModel()->getAttributes());
        return array_merge($categories, [
            'default_name' => $this->getAttribute('name'),
            'default_icon' => $this->getBase64file(),
            'category_info' => ['name' => $this->category->name, 'info' => implode(' / ', $this->category->getCategoryNamePath(true))],
            'location' => $this->location ? implode(' / ', $this->location->getLocationPath()) : '',
            'mobile_icon' => $this->getBase64file(ImgBehavior::IMG_MOBILE)
        ]);

    }


}