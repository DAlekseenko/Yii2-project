<?php

namespace console\models;

use Yii;

class CategoriesTmp extends Categories
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

		$sql = 'update ' . Categories::tableName() . ' ' .
			'set ' . implode(', ', $set) . ' ' .
			'from ' . self::tableName() . ' ' .
			'where ' . Categories::tableName() . '.id = ' . self::tableName() . '.id' .
			'';
		$countUpdate = Yii::$app->db->createCommand($sql)->execute();

		$sql = 'update ' . Categories::tableName() . ' ' .
			'set date_removed = \'' . date('Y-m-d H:i:s') . '\' ' .
			'where id not in (select id from ' . self::tableName() . ') and date_removed is null' .
			'';
		$countRemoved = Yii::$app->db->createCommand($sql)->execute();

		$sql = 'select id from ' . self::tableName() . ' ' .
			'left join ' . Categories::tableName() . ' tC using(id) ' .
			'where tC.id is null' .
			'';
		$newIds = Yii::$app->db->createCommand($sql)->queryColumn();

		if ($newIds) {
			$sql = 'insert into ' . Categories::tableName() . ' ' .
				'select * from ' . self::tableName() . ' tCT ' .
				'where tCT.id in (' . implode(', ', $newIds) . ')' .
				'';
			Yii::$app->db->createCommand($sql)->execute();
		}
		return [$countUpdate, $countRemoved, count($newIds)];
	}

	public static function createTable()
	{
		$sql = 'create table ' . self::tableName() . ' (like ' . Categories::tableName() . ' INCLUDING DEFAULTS)';
		Yii::$app->db->createCommand($sql)->execute();

		$sql = 'create index cat_lft_rgt ON ' . self::tableName() . ' USING btree (lft, rgt);';
		Yii::$app->db->createCommand($sql)->execute();

		$sql = 'alter table ' . self::tableName() . ' add PRIMARY KEY(id)';
		Yii::$app->db->createCommand($sql)->execute();
	}

	public static function dropTable()
	{
		$sql = 'drop table ' . self::tableName();
		Yii::$app->db->createCommand($sql)->execute();
	}

	public function getParentsWithoutCache($addCurrent = false)
	{
		$parents = $this->parents()->andWhere('date_removed IS NULL')->all();
		if ($addCurrent) {
			$parents[] = $this;
		}
		return $parents;
	}
}