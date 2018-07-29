<?php

namespace api\models\admin;

use common\models\Categories;
use common\components\behaviors\ImgBehavior;
use common\models\CategoriesInfo;


class AdminCategories extends Categories implements \JsonSerializable
{
    public $uploadImage,$infoModel;


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

    public function getCategoriesInfo()
    {
        return $this->hasOne(CategoriesInfo::className(), ['key' => 'key']);
    }

    public function getInfoModel()
    {
        if ($this->infoModel === null) {
            $this->infoModel = $this->categoriesInfo ?: new CategoriesInfo(['key' => $this->key]);
        }
        return $this->infoModel;
    }

    public function jsonSerialize()
    {
        $parent = $this->parents(1)->one();
        $root = self::findById($this->tree_id);

        $categories = array_merge($this->getAttributes(), $this->getInfoModel()->getAttributes());
        return array_merge($categories, [
            'default_name' => $this->getAttribute('name'),
            'default_icon' => $this->getBase64file(),
            'root' => $root ? [$root['id'] => $root['name']] : [],
            'parent' => $parent ? [$parent['id'] => $parent['name']] : [],
            'mobile_icon' => $this->getBase64file(ImgBehavior::IMG_MOBILE)
        ]);
    }


}