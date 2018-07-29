<?php
namespace frontend\modules\mobile\assets;

class AppAsset extends \frontend\assets\AppAsset
{
	public $css = [
		'/css/normalize.css',
		'/css/lib/jquery-ui.custom.css',
		'/css/elements.css',
		'/css/pointer.css',
		'/css/popup.css',
		'/css/main.css',
		'/css/frontend/site.css',
		'/css/frontend/form.css',
		'/css/frontend/mobile/site-mobile.css',
		'/css/frontend/mobile/form-mobile.css',
		'/css/frontend/media.css',
		'/css/frontend/mobile/media.css',
		'/css/modifiers.css',
		'/css/frontend/mobile/modifiers.css',
	];

	function init()
	{
		if (empty(\Yii::$app->params['enableMinify'])) {
			$this->depends[] = 'frontend\modules\mobile\assets\RequirejsAsset';
		} else {
			if ($this->basePath && file_exists(dirname($this->basePath) . '/css/mobile.min.css')) {
				$this->css = ['/css/mobile.min.css'];
			}
			$this->depends[] = 'frontend\modules\mobile\assets\RequirejsMinifyAsset';
		}
		parent::init();
	}
}
