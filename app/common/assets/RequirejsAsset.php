<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace common\assets;

use common\components\services\JsSettings;
use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class RequirejsAsset extends AssetBundle
{
	public $basePath = '@webroot';
	public $baseUrl = '@web';
	public $css = [];
	public $js = [
		'/js/lib/require.js',
	];
	public $jsOptions = [
		'id' => 'requireJs',
	];
	public $coreJs;
	public $defaultPagesPath = '@webroot/js/pages/';
	public $pagesPath;

	public function init()
	{
		parent::init();
		$this->jsOptions['data-main'] = $this->coreJs;
	}

	public function registerAssetFiles($view)
	{
		$this->jsOptions['data-settings'] = json_encode($this->getSettings($view));
		parent::registerAssetFiles($view);
	}

	private function getSettings($view)
	{
		$settings = JsSettings::get();
		$settings['isGuest'] = \Yii::$app->user->getIsGuest();

		if ($scriptName = $this->getScriptName($view, \Yii::getAlias($this->pagesPath))) {
			$settings['pageScript'] = basename($this->pagesPath) . '/' . $scriptName;
		} elseif ($scriptName = $this->getScriptName($view, \Yii::getAlias($this->defaultPagesPath))) {
			$settings['pageScript'] = basename($this->defaultPagesPath) . '/' . $scriptName;
		}

		return $settings;
	}

	private function getScriptName($view, $folder)
	{
		$scriptName = $view->context->id . '-' . $view->context->action->id;
		return file_exists($folder . $scriptName . '.js') ? $scriptName : false;
	}
}
