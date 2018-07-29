<?php

namespace console\models;

use Yii;

class ServicesTmp extends Services
{
	public static function tableName()
	{
		return parent::tableName() . '_tmp';
	}

	public static function loadInTable()
	{
		$set = [];
		foreach (self::getTableSchema()->columnNames as $columnName) {
			$set[] = $columnName . ' = ' . self::tableName() . '.' . $columnName;
		}

		$sql = 'update ' . Services::tableName() . ' ' .
			'set ' . implode(', ', $set) . ' ' .
			'from ' . self::tableName() . ' ' .
			'where ' . Services::tableName() . '.id = ' . self::tableName() . '.id' .
			'';
		$countUpdate = Yii::$app->db->createCommand($sql)->execute();

		$sql = 'update ' . Services::tableName() . ' ' .
			'set date_removed = \'' . date('Y-m-d H:i:s') . '\' ' .
			'where id not in (select id from ' . self::tableName() . ') and date_removed is null' .
			'';
		$countRemoved = Yii::$app->db->createCommand($sql)->execute();

		$sql = 'select id from ' . self::tableName() . ' ' .
			'left join ' . Services::tableName() . ' tS using(id) ' .
			'where tS.id is null' .
			'';
		$newIds = Yii::$app->db->createCommand($sql)->queryColumn();

		if ($newIds) {
			$sql = 'insert into ' . Services::tableName() . ' ' .
				'select * from ' . self::tableName() . ' tST ' .
				'where tST.id in (' . implode(', ', $newIds) . ')' .
				'';
			Yii::$app->db->createCommand($sql)->execute();
		}
		return [$countUpdate, $countRemoved, count($newIds)];
	}

	public static function createTable()
	{
		$sql = 'create table ' . self::tableName() . ' (like ' . Services::tableName() . ' INCLUDING DEFAULTS)';
		Yii::$app->db->createCommand($sql)->execute();

		$sql = 'alter table ' . self::tableName()  . ' add PRIMARY KEY(id)';
		Yii::$app->db->createCommand($sql)->execute();
	}

	public static function dropTable()
	{
		$sql = 'drop table ' . self::tableName();
		Yii::$app->db->createCommand($sql)->execute();
	}

	/**
	 * @return \common\models\CategoriesQuery
	 */
	public function getCategory()
	{
		return $this->hasOne(CategoriesTmp::className(), ['id' => 'category_id']);
	}

	public static function findServiceInterval($categoryId)
	{
		$pdo = yii::$app->db->getMasterPdo();
		$tableName = ServicesTmp::tableName();

		$query = "
			SELECT min(s_order),max(s_order)
 			FROM $tableName WHERE $categoryId = ANY(path) AND date_removed IS NULL";

		return $pdo->query($query)->fetch();
	}
}
