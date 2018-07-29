<?php

namespace frontend\controllersAbstract;

use common\models\Documents;
use frontend\modules\desktop\components\behaviors\RenderLayout;

/**
 * @mixin RenderLayout
 */
class HelpController extends AbstractController
{
	public function behaviors()
	{
		return [
			//в шаблоне используются функции из этого поведения, что бы рисовать разные части страницы
			'renderLayout' => [
				'class' => RenderLayout::className(),
			],
		];
	}

	public function actionUserAgreement()
	{
		$document = Documents::findByKey(Documents::KEY_RULES);
		return $this->render('//partial/help/userAgreement', ['document' => $document]);
	}

	public function actionAbout()
	{
		return $this->render('//partial/help/about', ['document' => Documents::findByKey(Documents::KEY_FAQ)]);
	}

	public function actionUssd()
	{
		return $this->render('//partial/help/ussd');
	}

	public function actionSocial()
	{
		return $this->render('//partial/help/social', ['document' => Documents::findByKey(Documents::KEY_SOCIAL)]);
	}
}
