<?php
namespace frontend\controllersAbstract;

use common\models\Categories;
use common\models\InvoicesUsersData;
use common\models\Services;
use frontend\components\behaviors\AjaxEmptyLayout;
use frontend\models\virtual\PageSearch;
use yii;
use yii\filters\AccessControl;

class InvoiceSearchController extends AbstractController
{

	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::className(),
				'rules' => [
					[
						'allow' => true,
						'roles' => ['@'],
					],
					[
						'allow' => false,
					],
				],
			],
			//запросы к этому контроллеру в мбильнике делаются не в попапе, а на новой странице. Поэтому для desctop мы отдает только контент(там в попапе)
			//а в мобильнике рисуем всю страницу.
			'ajaxEmptyLayout' => [
				'class' => AjaxEmptyLayout::className(),
			],
		];
	}

	//страница в попапе с родительскими категориями. Показывается при нажатии на кнопку Добавить
	public function actionIndex()
	{
		$params = [
			'categories' => Categories::findGlobalByShow(Categories::FIELD_SHOW_TOP)
		];
		return $this->render('index', $params);
	}

	//поиск в попапе при добавлении привязки
	public function actionSearch($value, $categoryId = null)
	{
		/**
		 * @var $categoriesSearch \common\models\virtual\CategoriesSearch
		 * @var $servicesSearch   \common\models\virtual\ServicesSearch
		 */
		list ($category, $categoriesSearch, $servicesSearch) = PageSearch::search($value, $categoryId);

		$params = [
			'value' => $value,
			'hint' => 'Найдено категорий: ' . $categoriesSearch->count() . ', услуг: ' . $servicesSearch->count(),
			'category' => $category,
			'categories' => $categoriesSearch->fetch(100),
			'services' => $servicesSearch->fetch(100),
		];

		return $this->render('index', $params);
	}

	//страница категории. Если в категории нет дочерних, то показываем страницу добавления привязки.
	public function actionCategory($categoryId)
	{
		$params = Categories::getClosestCategories($categoryId);
		if (!$params['closestChildren']) {
			if (!$params['category']['services']) {
				throw new yii\web\BadRequestHttpException('Bad categoryId');
			}
			return $this->renderCategoryServices($params, $params['category']['services'][0]);
		}

		$params = [
			'category' => $params['category'],
			'services' => $params['category']['services'],
			'categories' => $params['closestChildren'],
			'topItems' => $params['category']->getTop3()
		];
		return $this->render('index', $params);
	}

	public function actionCategoryServices($serviceId = null)
	{
		$service = Services::findById($serviceId, true);
		return $this->renderCategoryServices(Categories::getClosestCategories($service['category_id']), $service);
	}

	//страница в попапе с формой добавления привязки(слева все услуги данной категории, справа форма)
	public function renderCategoryServices($categoryClosest, $activeService)
	{
		$globalCategory = Categories::getGlobalByCategoryId($categoryClosest['category']['id']);
		$params = [
			'activeService' => $activeService,
			'globalCategory' => $globalCategory,
			'category' => $categoryClosest['category'],
			'services' => $categoryClosest['category']['services'],
			'model' => new InvoicesUsersData()
		];
		return $this->render('categoryServices', $params);
	}
}