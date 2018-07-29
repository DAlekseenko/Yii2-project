<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\modules\desktop\components\behaviors\RenderLayout
 * @var $favoriteItem \common\models\PaymentFavorites
 */

use yii\helpers\Url;
use common\helpers\Html;

$escapedName = htmlspecialchars($favoriteItem->name);
$this->title = 'МТС Деньги - Избранные платежи - ' . $escapedName;
$this->params['header'] = $escapedName;

$context = $this->context;
$context->getBreadcrumbsLayout()
		->appendBreadcrumb('Все платежи', Url::to('/payments'))
		->appendBreadcrumb('Избранные платежи', Url::to('/payments/favorites'))
		->appendBreadcrumb($favoriteItem->name);
?>
<div class="payment-result -col-2 -mobile-padding">

	<?php foreach($favoriteItem->getFieldsMap() as $i => $field): if (isset($field['name'], $field['value'])): ?>
		<div class="payment-result_item">
			<h5 class="-pb3"><?= htmlspecialchars($field['name']) ?></h5>
			<h3><?= htmlspecialchars($field['value']) ?></h3>
		</div>
	<?php endif; endforeach;?>

	<div class="payment-result_item -full-row">
		<h5 class="-pb3">Название</h5>
		<?= $this->render('//partial/site/addFavoriteForm', ['addFavorite' => $favoriteItem]) ?>
	</div>

	<div class="payment-result_item -no-title -full-row">
		<a href="<?= Url::to(['/payments/pay', 'id' => $favoriteItem->service_id, 'favId' => $favoriteItem->id]) ?>">
			<?= Html::mtsButton('Повторить платеж', ['class' => 'mts-button-print'])?>
		</a>
	</div>
</div>
<script id="alertTemplate" type="text/template">
	<div class="popup-alert"><div class="{{#if success}}success-message{{else}}error-message{{/if}}">{{text}}</div>
</script>