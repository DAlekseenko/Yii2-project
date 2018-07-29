<?php
/**
 * @var $this yii\web\View
 * @var $context \yii\web\Controller|\frontend\modules\desktop\components\behaviors\RenderLayout
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $searchModel \common\models\PaymentTransactionsSearch
 * @var $addFavorite \common\models\PaymentTransactionsSearch
 */
use yii\helpers\Url;

$this->title = 'МТС Деньги - История платежей';
$this->params['header'] = 'История платежей';

$context = $this->context;
$context->getBreadcrumbsLayout()
		->appendBreadcrumb(['label' => 'Все платежи', 'url' => Url::to('/payments')])
		->appendBreadcrumb('История платежей');
?>
<form id="paymentsHistoryForm" class="payments-history-form">
	<div class="payments-history-form_date">
		Период с
		<label for="paymentHistoryDateFrom" class="mts-date-field"><input type="text" id="paymentHistoryDateFrom" class="mts-date-field_input" name="filter[dateFrom]" value="<?= $searchModel->dateFrom ?>"></label>
		по
		<label for="paymentHistoryDateTo" class="mts-date-field"><input type="text" id="paymentHistoryDateTo" class="mts-date-field_input" name="filter[dateTo]" value="<?= $searchModel->dateTo ?>"></label>
		<button class="mts-button"><span>Показать</span></button>
	</div>

	<div id="paymentsHistoryPeriodLinks" class="payments-history-form_period">
		<a class="-dashed payments-history-form_period_link" data-period="-7d">неделя</a>
		<a class="-dashed payments-history-form_period_link" data-period="-1m">месяц</a>
		<a class="-dashed payments-history-form_period_link" data-period="-3m">3 месяца</a>
		<a class="-dashed payments-history-form_period_link" data-period="-6m">6 месяцев</a>
	</div>
</form>

<?php
$settings = [
	'dataProvider' => $dataProvider,
	'itemView' => '_historyItemView',
	'summary' => $this->render('_historySummary', ['searchModel' => $searchModel]),
	'options' => ['id' => 'paymentsHistoryGrid', 'class' => 'mts-list-view'],
	'itemOptions' => ['class' => 'mts-list-view_item'],
	'emptyText' => 'У Вас нет ни одного платежа, соответствующего выбранным параметрам.',
	'emptyTextOptions' => ['class' => 'empty-text'],
	'pager' => [
		'class' => \common\components\widgets\LinkPager::className(),
		'options' => ['class' => 'pagination', 'id' => 'historyPagination'],
	],
];
?>
<?= \yii\widgets\ListView::widget($settings); ?>

<script id="addFavoriteTemplate" type="text/template">
	<?= $this->render('//partial/site/addFavoriteForm', ['addFavorite' => $addFavorite]) ?>
</script>
<script id="alertTemplate" type="text/template">
	<div class="popup-alert"><div class="{{#if success}}success-message{{else}}error-message{{/if}}">{{text}}</div></div>
</script>
