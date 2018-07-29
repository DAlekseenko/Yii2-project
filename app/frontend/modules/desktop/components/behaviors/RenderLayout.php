<?php

namespace frontend\modules\desktop\components\behaviors;

use yii;
use common\models\layouts\LayoutFactory;
use common\models\layouts\PartialLayout;
use common\models\layouts\BreadcrumbsLayout;
use frontend\modules\desktop\models\layouts\AsideLayout;
use frontend\models\layouts\LoginLayout;


/**
 * @method PartialLayout getHeaderLayout()
 * @method PartialLayout getLocationLayout()
 * @method BreadcrumbsLayout getBreadcrumbsLayout()
 * @method LoginLayout getLoginLayout()
 * @method \frontend\modules\desktop\models\layouts\AsideLayout getAsideLayout()
 *
 * @method string renderHeader()
 * @method string renderLocation()
 * @method string renderBreadcrumbs()
 * @method string renderLogin()
 * @method string renderAside()
 */
class RenderLayout extends \common\components\behaviors\RenderLayout
{
	protected function setLayoutFactory()
	{
		$this->layoutFactory = new LayoutFactory();
		$this->layoutFactory->setLayout('aside', AsideLayout::class);
		$this->layoutFactory->setLayout('header', function () {
			return new PartialLayout('/layouts/_header.php');
		});
		$this->layoutFactory->setLayout('location', function () {
			return new PartialLayout('/layouts/_location.php');
		});
		$this->layoutFactory->setLayout('breadcrumbs', function () {
			return new BreadcrumbsLayout('/layouts/_breadcrumbs.php');
		});
		$this->layoutFactory->setLayout('login', function () {
			return new LoginLayout('//partial/site/login.php');
		});
	}
}