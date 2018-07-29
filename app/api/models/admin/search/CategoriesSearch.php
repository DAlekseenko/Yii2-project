<?php

namespace api\models\admin\search;

use yii\db\ActiveQuery;
use api\models\admin\AdminCategories as Categories;
use common\models\CategoriesInfo;

class CategoriesSearch extends Categories
{
    public $keywords, $show_main, $offset = 0;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['show_main', 'boolean'],
            [['id', 'name', 'keywords', 'offset'], 'safe'],
            ['id', 'match', 'pattern' => '/^\d+/iu'],
        ];
    }

    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels['keywords'] = 'Ключевые слова';
        $labels['show_main'] = 'Отображение';
        return $labels;
    }

    /**
     * @return ActiveQuery
     */
    public function getQuery()
    {
        $cat = Categories::tableName();

        return self::find()->joinWith('categoriesInfo')->where("$cat.date_removed IS NULL");
    }

    /**
     * @param $query
     * @return ActiveQuery
     */
    public function frontSearch(ActiveQuery $query)
    {
        $cat = Categories::tableName();
        $keywords = trim($this->keywords);
        $catInfo = CategoriesInfo::tableName();

        $query->andFilterWhere(['id' => $this->id]);

        $condition = [
            'or',
            ['ilike', "$cat.name", $keywords],
            ['ilike', "$catInfo.name", $keywords],
            ['ilike', "$catInfo.description", $keywords],
            ['ilike', "$catInfo.description_short", $keywords],
        ];
        $query->andFilterWhere($condition);

        $query->offset($this->offset)->limit(100);
        $query->orderBy("$cat.level");

        return $query;

    }


}
