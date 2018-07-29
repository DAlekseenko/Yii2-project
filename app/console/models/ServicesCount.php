<?php

namespace console\models;

use Yii;

/**
 * This is the model class for table "services_count".
 *
 * @property integer $category_id
 * @property integer $location_id
 * @property integer $count
 *
 * @property Categories $category
 * @property Locations $location
 */
class ServicesCount extends \common\models\ServicesCount
{

	public static function recount($serviceTable, $categoryTable)
	{
		self::deleteAll();
		self::insertCounts($serviceTable); //после этого в таблице содержатся количество услуг по каждой категории в кажддом регионе
		self::updateLocationCounts(); //после этого в таблице содержатся количество услуг по каждой категории в каждом регионе + в дочерних
		self::updateCountsWithGlobalLocation(); //после этого в таблице содержатся количество услуг по каждой категории в каждом регионе + в дочерних, включая услуги на все регионы
		self::updateCategoryCounts($categoryTable); //после этого в таблице содержатся количество услуг по каждой категории + в дочерних в каждом регионе + в дочерних
	}

	//заносим в таблицу значения по каждой категории(не включая дочерние)
	private static function insertCounts($serviceTable)
	{
		$sql = 'insert into ' . self::tableName() . ' (category_id, location_id, count) ' .
			'select category_id, location_id, count(*) from ' . $serviceTable . ' group by category_id, location_id';
		Yii::$app->getDb()->createCommand($sql)->execute();
	}

	private static function updateCountsWithGlobalLocation()
	{
		self::createTemporary();

		$sqlCount = 'select t.category_id, tL.id, t.count ' .
			'from ' . self::tableName() . ' t ' .
			'join ' . Locations::tableName() . ' tL on (1 = 1) ' .
			'where t.location_id = 0 ' .
			'';

		//заносим эти количества в вспомогательную таблицу
		$sql = 'insert into temporary_services (category_id, location_id, count) ' . $sqlCount;
		Yii::$app->getDb()->createCommand($sql)->execute();

		$sql = 'delete from ' . self::tableName() . ' where location_id = 0';
		Yii::$app->getDb()->createCommand($sql)->execute();

		self::loadFromTemporary();
		self::dropTemporary();
	}

	//поднимаемся на уровень вверх до упора и суммируем дочерние значения
	private static function updateCategoryCounts($categoryTable)
	{
		//после этого цикла в таблице будет содержаться количество для данной категории и ВСЕХ дочерних для данного города
		$maxLevel = Yii::$app->db->createCommand('select max(level) from ' . $categoryTable)->queryScalar();
		while ($maxLevel) {
			self::createTemporary();

			//суммируем все количества в таблице services_count по каждому уровню
			$sqlCount = 'select tSiblings.id, tChildren.location_id, sum(tChildren.count) ' .
				'from ' . $categoryTable . ' tC ' .
				'join ' . $categoryTable . ' tSiblings on (tSiblings.id = tC.parent_id) ' .
				'join ' . self::tableName() . ' tChildren on (tC.id = tChildren.category_id) ' .
				'where tC.level = ' . $maxLevel . ' ' .
				'group by tSiblings.id, tChildren.location_id';

			//заносим эти количества в вспомогательную таблицу
			$sql = 'insert into temporary_services (category_id, location_id, count) ' . $sqlCount;
			Yii::$app->getDb()->createCommand($sql)->execute();

			self::loadFromTemporary();
			self::dropTemporary();
			$maxLevel--;
		}
	}

	private static function updateLocationCounts()
	{
		//после этого цикла в таблице будет содержаться количество для данной категории и ВСЕХ дочерних для данного города и ВСЕХ дочерних
		$maxLevel = Locations::find()->max('level');
		while ($maxLevel) {
			self::createTemporary();

			//суммируем все количества в таблице services_count по каждому уровню
			$sqlCount = 'select tSiblings.id, tChildren.category_id, sum(tChildren.count) ' .
				'from ' . Locations::tableName() . ' tC ' .
				'join ' . Locations::tableName() . ' tSiblings on (tSiblings.id = tC.parent_id) ' .
				'join ' . self::tableName() . ' tChildren on (tC.id = tChildren.location_id) ' .
				'where tC.level = ' . $maxLevel . ' ' .
				'group by tSiblings.id, tChildren.category_id';

			//заносим эти количества в вспомогательную таблицу
			$sql = 'insert into temporary_services (location_id, category_id, count) ' . $sqlCount;
			Yii::$app->getDb()->createCommand($sql)->execute();

			self::loadFromTemporary();
			self::dropTemporary();
			$maxLevel--;
		}
	}

	private static function createTemporary()
	{
		//создаем вспомогательную таблицу. В ней category_id+location_id - это категория, в которую ДОБАВЛЯТЬ значения. count - значения, которое будет добавлено
		$sql = 'create temporary table temporary_services (like ' . self::tableName() . ')';
		Yii::$app->getDb()->createCommand($sql)->execute();
	}

	private static function loadFromTemporary()
	{
		//забираем их из этой вспомогательной таблицы
		//апдейтим те записи, которые в services_count уже были
		$sql = 'update ' . self::tableName() . ' t ' .
			'set count = t.count + tC.count ' .
			'from temporary_services tC ' .
			'where (t.category_id = tC.category_id) and (t.location_id = tC.location_id) ' .
			'';
		Yii::$app->getDb()->createCommand($sql)->execute();

		//инсертим те записи, которые в services_count отсутствовали
		$sql = 'insert into ' . self::tableName() . ' (location_id, category_id, count) ' .
			'select t.location_id, t.category_id, t.count ' .
			'from temporary_services t ' .
			'left join ' . self::tableName() . ' tS on (t.category_id = tS.category_id) and (t.location_id = tS.location_id) ' .
			'where tS.location_id is null' . //проверка на осутствие записи в другой таблице
			'';
		Yii::$app->getDb()->createCommand($sql)->execute();
	}

	private static function dropTemporary()
	{
		//дропаем вспомогательную таблицу, что бы в следующий уровень попало количество, поссчитанное на этом уровне
		$sql = 'drop table temporary_services';
		Yii::$app->getDb()->createCommand($sql)->execute();
	}
}