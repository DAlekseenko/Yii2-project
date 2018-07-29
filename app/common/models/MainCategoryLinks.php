<?php

namespace common\models;

/**
 * @property integer $id
 * @property integer $main_category_id
 * @property string $key
 * @property integer $service_id
 * @property string $path
 * @property integer $order
 *
 * @property Categories category
 * @property Services service
 */
class MainCategoryLinks extends \yii\db\ActiveRecord implements \JsonSerializable
{
    public $subLink;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'main_category_links';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['main_category_id'], 'required'],
            [['id', 'main_category_id', 'service_id'], 'integer'],
            [['key'], 'string', 'max' => 32],
            [['subLink'], 'safe'],
            [['subLink'], 'linkValidator'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'subLink' => 'Ссылка',
        ];
    }


    public function linkValidator()
    {
        $category = Categories::findById($this->subLink);
        $service = Services::findById($this->subLink);
        if (empty($category) && empty($service)) {
            $this->addError('subLink', 'Ссылка не найдена');
            return false;
        }
        if (!empty($category)) {
            $this->key = $category->key;
            $path = $category->getCategoryNamePath(true);
            $this->path = implode("/", $path);
            if (!empty(self::getBind($this->main_category_id, $category->key))) {
                $this->addError('subLink', 'Данная ссылка уже существует');
                return false;
            }
        }
        if (!empty($service)) {
            $this->service_id = $service->id;
            if (!empty(self::getBind($this->main_category_id, $service->id))) {
                $this->addError('subLink', 'Данная ссылка уже существует');
                return false;
            }
        }
        return true;
    }


    public function getCategory()
    {
        return $this->hasOne(Categories::class, ['key' => 'key'])->andWhere('date_removed IS NULL');
    }

    public function getService()
    {
        return $this->hasOne(Services::class, ['id' => 'service_id']);
    }

    public function getBinding()
    {
        $bind = [];
        if ($this->service && $this->service->date_removed) {
            $bind['remove_description'] = 'Сервис удален ' . $this->service->date_removed;
        }
        if (!$this->service && !$this->category) {
            $bind['remove_description'] = ($this->path ? 'По данному пути:' . $this->path : '') . 'Категория не найдена';
        }
        if (!isset($bind['remove_description'])) {
            $bind = $this->service ?: $this->category;
            $bind = $bind->getAttributes();
        }
        return $bind;
    }

    public static function getBind($mainCategoryId, $key)
    {
        $where = ['key' => $key];
        if (preg_match('/^\d+$/', $key)) {
            $where = ['service_id' => $key];
        }
        return self::find()
            ->where(['main_category_id' => $mainCategoryId])
            ->andWhere($where)->one();
    }

    public function jsonSerialize()
    {
        return array_merge($this->getBinding(), ['link_id' => $this->id, 'path' => $this->path]);
    }

}
