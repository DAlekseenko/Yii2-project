<?php
namespace api\models\admin\search;

use common\models\ServicesLists;


class ServicesListsSearch extends ServicesLists
{

    /**
     * @param $params
     * @return object|\yii\db\ActiveQuery $query
    */
    public function search($params)
    {
        $table = self::tableName();

        $query = ServicesLists::find()->with('service');

        if (!empty($params['service_id'])) {
            $query->andFilterWhere(["$table.service_id" => (int)$params['service_id']]);
        }

        if (!empty($params['list_name'])) {
            $query->andFilterWhere(["$table.list_name" => (string)$params['list_name']]);
        }
        return $query;
    }

    public function countAll()
    {
        return ServicesLists::find()->count();
    }

}