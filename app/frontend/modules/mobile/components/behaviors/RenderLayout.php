<?php

namespace frontend\modules\mobile\components\behaviors;

use frontend\models\layouts\LoginLayout;
use frontend\modules\mobile\models\layouts\FooterLayout;
use frontend\modules\mobile\models\layouts\TopPanel;
use yii;
use common\models\layouts\LayoutFactory;
use common\models\layouts\PartialLayout;
use common\models\layouts\BreadcrumbsLayout;
use frontend\modules\mobile\models\layouts\UserMenu;

/**
 * @method PartialLayout getHeaderLayout()
 * @method PartialLayout getLocationLayout()
 * @method BreadcrumbsLayout getBreadcrumbsLayout()
 * @method BreadcrumbsLayout getTopPanelLayout()
 * @method LoginLayout getLoginLayout()
 * @method LoginLayout getUserMenuLayout()
 *
 * @method string renderHeader()
 * @method string renderFooter()
 * @method string renderTopPanel()
 * @method string renderLocation()
 * @method string renderBreadcrumbs()
 * @method string renderLogin()
 * @method string renderUserMenu()
 */
class RenderLayout extends \common\components\behaviors\RenderLayout
{
	protected function setLayoutFactory()
	{
		$this->layoutFactory = new LayoutFactory();
		$this->layoutFactory->setLayout('location', function () {
			return new PartialLayout('/layouts/_location.php');
		});
		//панелька авторизации ЛИБО панелька данных о пользователе(phone + balance)
		$this->layoutFactory->setLayout('topPanel', TopPanel::class);
		$this->layoutFactory->setLayout('breadcrumbs', function () {
			return new BreadcrumbsLayout('/layouts/_breadcrumbs.php');
		});
		$this->layoutFactory->setLayout('header', function () {
			return new PartialLayout('/layouts/_header.php');
		});
		$this->layoutFactory->setLayout('login', function () {
			return new LoginLayout('//partial/site/login.php');
		});
		//часть от общего меню справа. Если isGuest, то оно отсутствует
		$this->layoutFactory->setLayout('userMenu', UserMenu::class);
		$this->layoutFactory->setLayout('footer', FooterLayout::class);
	}
}