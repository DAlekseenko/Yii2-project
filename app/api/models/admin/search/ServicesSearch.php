<?php

namespace api\models\admin\search;

use yii\db\ActiveQuery;
use common\models\ServicesInfo;
use api\models\admin\AdminServices AS Services;

class ServicesSearch extends Services
{
    public $keywords, $is_global, $offset = 0;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_global'], 'boolean'],
            [['id', 'name', 'keywords', 'offset'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels['keywords'] = 'Ключевые слова';
        $labels['is_global'] = 'Показывать в начислениях';
        return $labels;
    }

    /**
     * @return ActiveQuery
     */
    public function getQuery()
    {
        $service = Services::tableName();

        return self::find()->joinWith('servicesInfo')->with('location', 'category.categoriesInfo')->where("$service.date_removed IS NULL");
    }

    public function frontSearch(ActiveQuery $query)
    {
        $service = Services::tableName();
        $keywords = trim($this->keywords);
        $serviceInfo = ServicesInfo::tableName();

        $query->andFilterWhere(['id' => $this->id]);
        if ($this->is_global) {
            $query->andFilterWhere(["$serviceInfo.is_global" => (bool)$this->is_global]);
        }

        $condition = [
            'or',
            ['ilike', "$service.name", $keywords],
            ['ilike', "$serviceInfo.name", $keywords],
            ['ilike', "$serviceInfo.description", $keywords],
            ['ilike', "$serviceInfo.description_short", $keywords],
        ];
        $query->andFilterWhere($condition);
        $query->offset($this->offset)->limit(100);

        return $query;

    }


}