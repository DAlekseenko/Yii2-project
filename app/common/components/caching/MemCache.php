<?php
namespace common\components\caching;

use yii;
use yii\caching\TagDependency;

class MemCache extends yii\caching\MemCache
{

	public function setWithDependency($key, $value, $tags, $duration = 86400)
	{
		$this->set($key, $value, $duration, new TagDependency(['tags' => (array)$tags]));
	}

	public function invalidateTags($tags)
	{
		TagDependency::invalidate($this, (array)$tags);
	}

	//это для того что бы сайт не падал если memcache упал
	protected function getValue($key)
	{
		try {
			return parent::getValue($key);
		} catch (\Exception $e) {
			return false;
		}
	}
}