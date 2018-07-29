<?php

namespace frontend\modules\common;

/**
 * @deprecated
 * @todo сделан, чтобы унифицировать проверку капчи на мобильной и десктопной витрине, выпелить как появится нормальный фронт
 */
class Module extends \yii\base\Module
{
	public $layout = 'main';
	public $controllerNamespace = 'frontend\modules\common\controllers';
}