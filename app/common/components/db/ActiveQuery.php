<?php
namespace common\components\db;

use yii;

use yii\caching\TagDependency;
use yii\db\connection;

//Все Query наследуются от этого. Этот класс добавляет возможность удобного кэширования запроса
class ActiveQuery extends yii\db\ActiveQuery
{
	private $cacheDuration = null;
	private $dependencyTags = [];

	public function init()
	{
		$modelClass = $this->modelClass;
		$this->dependencyTags = [$modelClass::tableName()];
		parent::init();
	}

	/**Использование: Users::find()->cache('qwer')->where(['id_user' => $id])->one()
	 * @param int|null $duration Seconds to cache; Use 0 to indicate that the cached data will never expire
	 * @return \common\components\db\ActiveQuery | static
	 */
	public function cache($duration = 86400)
	{
		$this->cacheDuration = $duration;
		return $this;
	}

	/**
	 * @param string|array $dependencyTags таги, от которых зависит кэшируемое
	 * @return \common\components\db\ActiveQuery | static
	 */
	public function addCacheTag($dependencyTags = [])
	{
		$this->dependencyTags = array_merge($this->dependencyTags, (array)$dependencyTags);
		return $this;
	}

	public function all($db = null)
	{
		return $this->run('all', null, $db);
	}

	public function one($db = null)
	{
		return $this->run('one', null, $db);
	}

	public function count($q = '*', $db = null)
	{
		return $this->run('count', $q, $db);
	}

	public function sum($q, $db = null)
	{
		return $this->run('sum', $q, $db);
	}

	public function max($q, $db = null)
	{
		return $this->run('max', $q, $db);
	}

	public function min($q, $db = null)
	{
		return $this->run('min', $q, $db);
	}

	public function average($q, $db = null)
	{
		return $this->run('average', $q, $db);
	}

	public function scalar($db = null)
	{
		return $this->run('scalar', null, $db);
	}

	public function column($db = null)
	{
		return $this->run('column', null, $db);
	}

	public function exists($db = null)
	{
		return $this->run('exists', null, $db);
	}

	private function run($functionName, $q, $db = null)
	{
		/**@var Connection $db */
		if ($db === null) {
			$modelClassName = $this->modelClass;
			$db = $modelClassName::getDb();
		}

		if ($this->cacheDuration === null) {
			return $q === null ? parent::$functionName($db) : parent::$functionName($q, $db);
		}

		return $db->cache(function (Connection $db) use ($functionName, $q) {

			return $q === null ? parent::$functionName($db) : parent::$functionName($q, $db);

		}, $this->cacheDuration, $this->dependencyTags ? new TagDependency(['tags' => $this->dependencyTags]) : null);
	}
}