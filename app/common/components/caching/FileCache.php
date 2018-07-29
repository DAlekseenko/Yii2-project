<?php
namespace common\components\caching;

use yii;
use yii\caching\TagDependency;

class FileCache extends yii\caching\FileCache
{
	public $cachePath = '@runtime/../../frontend/runtime/cache';

	public function setWithDependency($key, $value, $tags, $duration = 86400)
	{
		$this->set($key, $value, $duration, new TagDependency(['tags' => (array)$tags]));
	}

	public function invalidateTags($tags)
	{
		TagDependency::invalidate($this, (array)$tags);
	}
}