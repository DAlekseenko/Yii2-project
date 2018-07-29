<?php
/**
 * @var $this yii\web\View
 * @var $context \yii\web\Controller|\frontend\modules\desktop\components\behaviors\RenderLayout
 * @var $dataProvider \yii\data\ActiveDataProvider
 */
use yii\helpers\Url;
use common\helpers\Html;

$this->title = 'МТС Деньги - Избранные платежи';
$this->params['header'] = 'Избранные платежи';

$context = $this->context;
$context->getBreadcrumbsLayout()
	->appendBreadcrumb('Все платежи', Url::to('/payments'))->appendBreadcrumb('Избранные платежи');
?>

<div class="favorites-hint">Сохраните платеж в избранном сразу после оплаты или из <?= Html::a('истории платежей', '/payments/history') ?>.</div>

<?= $this->render('//partial/payments/favorites', ['settings' => [
	'dataProvider' => $dataProvider,
]])?>