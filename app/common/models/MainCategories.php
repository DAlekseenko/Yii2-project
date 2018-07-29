<?php

namespace common\models;

use api\components\formatters\EntitiesFormatter;
use common\models\sql\CategoriesSearchSql;
use \yii\db\ActiveRecord;

/**
 * @property integer $id
 * @property integer $c_order
 * @property integer $key
 * @property integer $custom_category_id
 * @property string $path
 * @property MainCategoryLinks [] $mainLinks
 */
class MainCategories extends ActiveRecord implements \JsonSerializable
{
    public $link;

    public $sublinksText;

    /** @var  Categories|CategoriesCustom */
    public $entity;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'main_categories';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['link'], 'safe'],
            [['c_order', 'link'], 'required'],
            [['id', 'c_order', 'custom_category_id'], 'integer'],
            [['key'], 'string', 'max' => 32],
            [['link'], 'linkValidator'],
            [['sublinksText'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'c_order' => 'Порядок',
            'link' => 'Категория',
            'sublinksText' => 'Ссылки',
            'path' => 'Путь'
        ];
    }


    public function linkValidator()
    {
        if ((int)$this->link < 10000000000) {
            $this->entity = CategoriesCustom::find()->where(['id' => (int)$this->link])->one();
        } else {
            $this->entity = Categories::findById($this->link);
        }
        if (empty($this->entity)) {
            $this->addError('link', 'Категория не найдена');
            return false;
        }
        return true;
    }

    public function beforeSave($insert)
    {
        if ($this->entity instanceof CategoriesCustom) {
            $this->custom_category_id = $this->entity->id;
        } else {
            $path = $this->entity->getCategoryNamePath(true);
            $this->path = implode("/", $path);
            $this->key = $this->entity->key;
        }
        return parent::beforeSave($insert);
    }


    public function afterFind()
    {
        if ($this->custom_category_id) {
            $this->entity = CategoriesCustom::find()->where(['id' => (int)$this->custom_category_id])->one();
        } else {
            $this->entity = Categories::find()->where(['key' => $this->key])->one();
        }
        $this->link = $this->entity ? $this->entity->id : null;
    }

    public function beforeDelete()
    {
        MainCategoryLinks::deleteAll(['main_category_id' => $this->id]);
        return parent::beforeDelete();
    }

    public static function getMain($location_id = null)
    {
        list($categoryIds, $serviceIds, $customCategoryIds, $mainCategoryLinks) = self::getBaseLists();

        $categories = Categories::find()->where(['id' => $categoryIds])->all();
        $categoriesCustom = CategoriesCustom::find()->where(['id' => $customCategoryIds])->withServices()->all();
        $categoriesCustomAssoc = [];
        foreach ($categoriesCustom as $categoryCustom) {
            $categoriesCustomAssoc[$categoryCustom->id] = $categoryCustom;
        }
        $categoriesCustom = $categoriesCustomAssoc;

        $services = Services::find()->with('servicesInfo')->where(['id' => $serviceIds, 'location_id' => Locations::getCurrentLocationTreeIds($location_id)])->all();
        $servicesAssoc = [];
        foreach ($services as $service) {
            $servicesAssoc[$service->id] = $service;
        }
        $services = $servicesAssoc;

        $selectEngine = new CategoriesSearchSql();

        $result = $selectEngine->resultCategoriesReduce($categories);
        $finalCategories = empty($result) ? [] : $result->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE | \PDO::FETCH_CLASS, Categories::class);

        $result = ['roots' => [], 'children' => []];
        foreach ($mainCategoryLinks as $parentCategoryId => $mainCategoryLink) {

            if (isset($finalCategories[$parentCategoryId])) {
                $finalCategories[$parentCategoryId]->id = $parentCategoryId;
                $result['roots'][] = $finalCategories[$parentCategoryId];
            } elseif (isset($categoriesCustom[$parentCategoryId]) && $categoriesCustom[$parentCategoryId]->services_count > 0) {
                $result['roots'][] = $categoriesCustom[$parentCategoryId];
            }

            foreach ($mainCategoryLink as $item) {
                if (isset($item['category_id']) && isset($finalCategories[$item['category_id']])) {
                    $finalCategories[$item['category_id']]->id = $item['category_id'];
                    if (!isset($result['children'][$parentCategoryId])) {
                        $result['children'][$parentCategoryId] = [];
                    }
                    if (count($result['children'][$parentCategoryId]) < 3) {
                        $result['children'][$parentCategoryId][] = $finalCategories[$item['category_id']];
                    }

                } else if (isset($item['service_id']) && isset($services[$item['service_id']])) {
                    if (!isset($result['children'][$parentCategoryId])) {
                        $result['children'][$parentCategoryId] = [];
                    }
                    if (count($result['children'][$parentCategoryId]) < 3) {
                        $result['children'][$parentCategoryId][] = $services[$item['service_id']];
                    }
                }
            }
        }

        return $result;
    }

    private static function getBaseLists()
    {
        $categoryIds = $serviceIds = [];
        $customCategoryIds = [];

        $mainCategoryLinks = self::getMainCategoryLinks();

        foreach ($mainCategoryLinks as $parent => $mainCategoryLink) {
            if ($parent < 10000000000) {
                $customCategoryIds[] = $parent;
            } else {
                $categoryIds[] = $parent;
            }
            foreach ($mainCategoryLink as $item) {
                if (isset($item['category_id'])) {
                    $categoryIds[] = $item['category_id'];
                    continue;
                }
                if (isset($item['service_id'])) {
                    $serviceIds[] = $item['service_id'];
                }
            }
        }

        return [$categoryIds, $serviceIds, $customCategoryIds, $mainCategoryLinks];
    }

    private static function getMainCategoryLinks()
    {
        $currentLocation = Locations::getCurrentLocation();

        /** Если текущая локация является областью, то сперва берем областные, если нет, то - городские */
        $order = $currentLocation->parent_id ? ' DESC ' : ' ASC ';

        $pdo = \yii::$app->db->getMasterPdo();
        $query = "
			SELECT CASE WHEN c.key IS NULL THEN  mc.custom_category_id ELSE  c.id END AS parent, sc.id AS category_id, mcl.service_id,
				CASE WHEN ss.location_id > 0 THEN ss.location_id ELSE {$currentLocation->id} END loc
				FROM main_categories mc
				LEFT JOIN categories c ON mc.key = c.key AND c.date_removed IS NULL
				LEFT JOIN main_category_links mcl ON mc.id = mcl.main_category_id
				LEFT JOIN categories sc ON mcl.key = sc.key AND sc.date_removed IS NULL
				LEFT JOIN services ss ON mcl.service_id = ss.id AND ss.date_removed IS NULL
			WHERE (mc.custom_category_id IS NOT NULL OR c.key IS NOT NULL) AND (mcl.service_id IS NOT NULL OR sc.key IS NOT NULL) ORDER BY mc.c_order, loc {$order}, mcl.order, mcl.id";

        return $pdo->query($query)->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_ASSOC);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMainLinks()
    {
        return $this->hasMany(MainCategoryLinks::class, ['main_category_id' => 'id'])
            ->with(['service', 'category'])
            ->orderBy('order');
    }


    public function jsonSerialize()
    {
        return array_merge($this->getAttributes(), [
            'entity_name' => $this->entity ? $this->entity->name : 'Категория не найдена',
            'binding' => $this->mainLinks,
            'link' => $this->link,
        ]);
    }
}
