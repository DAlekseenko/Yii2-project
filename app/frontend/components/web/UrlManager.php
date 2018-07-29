<?php
namespace frontend\components\web;

class UrlManager extends \yii\web\UrlManager
{
	public $enablePrettyUrl = true;
	public $showScriptName = false;
	public $rules = [
		'common/<controller:.+>/<action:.+>' => 'common/<controller>/<action>',
		'site/captcha' => 'common/site/captcha',
		'mobile/site/captcha' => 'common/site/captcha',
		'mobile/<controller:.+>/<action:.+>' => 'mobile/<controller>/<action>',
		'mobile/<controller:.+>' => 'mobile/<controller>/index',
		'<controller:.+>/<action:.+>' => 'desktop/<controller>/<action>',
		'<controller:.+>' => 'desktop/<controller>/index',
		'' => 'desktop/site/index'
	];

	public function parseRequest($request)
	{
		$result = parent::parseRequest($request);
		if (!empty($result[0]) && substr($result[0], 0, 7) === 'desktop' && $this->isMobileSite()) {
			$result[0] = 'mobile' . substr($result[0], 7);
		}
		return $result;
	}

	private function isMobileSite()
	{
		//если имеется кука, то она определяет как будет выглядеть сайт
		if (isset($_COOKIE['siteType'])) {
			$siteType = array_search($_COOKIE['siteType'], \Yii::$app->params['siteTypes']);
			return $siteType === 'mobile';
		}

		return \Yii::$app->clientDevice->mobileDetect->isMobile() && !\Yii::$app->clientDevice->mobileDetect->isTablet();
	}
}