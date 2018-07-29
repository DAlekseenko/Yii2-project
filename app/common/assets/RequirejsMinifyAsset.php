<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace common\assets;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class RequirejsMinifyAsset extends \common\assets\RequirejsAsset
{
	protected $minifiedJs;

	public function registerAssetFiles($view)
	{
		$file = ROOT_DIR . 'htdocs/' . $this->minifiedJs;

		if (!$this->minifiedJs || !file_exists($file)) {
			parent::registerAssetFiles($view);
		} else {
			unset($this->jsOptions['data-main']);
			parent::registerAssetFiles($view);
			$view->registerJsFile($view->getAssetManager()->getAssetUrl($this, $this->minifiedJs . '?' . md5(file_get_contents($file))));
		}
	}
}
