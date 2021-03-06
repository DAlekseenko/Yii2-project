<?php

namespace common\models;

use common\components\behaviors\ImgBehavior;
use common\components\db\ActiveQuery;
use common\models\sql\CategoriesSearchSql;
use api\components\formatters\EntitiesFormatter;
use yii\helpers\Url;

/**
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property string $description_short
 * @property string $identifier_name
 *
 * @property Categories[] $categories
 */
class CategoriesCustom extends Categories implements \JsonSerializable
{

    public $parent_id = null;

    public $level = 0;

    public $servicesCountHash = null;

    public $uploadImage;

    public function getUkey()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'categories_custom';
    }

    /**
     * @inheritdoc
     * @return CategoriesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CategoriesCustomQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['id'], 'integer'],
            [['name', 'description', 'description_short'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'description' => 'Описание',
            'description_short' => 'Кроткое описание'
        ];
    }

    public function behaviors()
    {
        return [
            'imgBehavior' => [
                'class' => ImgBehavior::class,
            ],
        ];
    }

    /**
     * @param $categoryId
     * @return array|Services
     */
    public static function findById($categoryId)
    {
        return static::find()->where(['id' => $categoryId])->with('services')->one();
    }

    public function getClosest($sortMode = self::SORT_MODE_ALPHABET)
    {
        $selectEngine = new CategoriesSearchSql($this);
        $selectEngine->setSortMode($sortMode);

        $result = $selectEngine->findCategories();

        return $result ? $result->fetchAll(\PDO::FETCH_CLASS, parent::class) : [];
    }

    public function getBreadcrumbs()
    {
        return [
            ['url' => Url::to(['/categories', 'id' => $this->id]), 'class' => '--breadcrumb-item', 'label' => $this->name, 'data-id' => $this->id]
        ];
    }

    public function getCategoryNamePath($addCurrent = false)
    {
        return $addCurrent ? [$this->name] : null;
    }

    public function getParents($addCurrent = false)
	{
		return [];
	}


	public function saveIcons($data)
    {
        foreach (array_keys(ImgBehavior::$images) as $type) {
            $this->uploadImage = $data[$type . '_icon'];
            $this->setFileFromBase64($type);
        }
        return true;
    }

    public function afterDelete()
    {
        foreach (ImgBehavior::$images as $type => $v) {
            if ($this->hasImg($type)) {
                unlink($this->getImgPath($type));
            }
        }
    }

    public function getCategoryLinks()
    {
        return $this->hasMany(CategoryCustomCategories::className(), ['custom_category_id' => 'id']);
    }

    public function getServiceLinks()
    {
        return $this->hasMany(CategoryCustomServices::className(), ['custom_category_id' => 'id']);
    }

    public function getCategories()
    {
        return $this->hasMany(Categories::className(), ['key' => 'key'])->andWhere('date_removed IS NULL')
            ->via('categoryLinks');
    }

    public function getServices()
    {
        return $this->hasMany(Services::className(), ['id' => 'service_id'])
            ->via('serviceLinks');
    }

    public function __get($name)
    {
        if ($name == 'services_count') {
            if (isset($this->servicesCountHash)) {
                return $this->servicesCountHash;
            }
            $sum = 0;
            $childrenCategory = new CategoriesSearchSql($this);
            $result = $childrenCategory->findCategories();
            foreach ($result ? $result->fetchAll() : [] as $category) {
                $sum += $category['services_count'];
            }

            $this->servicesCountHash = count($this->services) + $sum;

            return $this->servicesCountHash;
        }
        return parent::__get($name); // TODO: Change the autogenerated stub
    }

    public function jsonSerialize()
    {
        return array_merge($this->getAttributes(), [
            'services' => EntitiesFormatter::serviceSetFormatter($this->services ?: []),
            'categories' => EntitiesFormatter::categorySetFormatter($this->categories ?: []),
            'default_icon' => $this->getBase64file(),
            'mobile_icon' => $this->getBase64file(ImgBehavior::IMG_MOBILE)
        ]);
    }
}
