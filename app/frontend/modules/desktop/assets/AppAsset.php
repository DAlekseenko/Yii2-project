<?php
namespace frontend\modules\desktop\assets;

class AppAsset extends \frontend\assets\AppAsset
{
	public $css = [
		'/css/normalize.css',
		'/css/lib/jquery-ui.custom.css',
		'/css/elements.css',
		'/css/pointer.css',
		'/css/popup.css',
		'/css/main.css',
		'/css/main-site.css',
		'/css/frontend/site.css',
		'/css/frontend/form.css',
		'/css/frontend/desktop/site-desktop.css',
		'/css/frontend/desktop/form-desktop.css',
		'/css/frontend/media.css',
		'/css/frontend/desktop/media.css',
		'/css/modifiers.css',
	];

	function init()
	{
		if (empty(\Yii::$app->params['enableMinify'])) {
			$this->depends[] = 'frontend\modules\desktop\assets\RequirejsAsset';
		} else {
			if ($this->basePath && file_exists(dirname($this->basePath) . '/css/desktop.min.css')) {
				$this->css = ['/css/desktop.min.css'];
			}
			$this->depends[] = 'frontend\modules\desktop\assets\RequirejsMinifyAsset';
		}

		parent::init();
	}
}
