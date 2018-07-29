<?php
/**
 * @var $this yii\web\View
 * @var $userShowLoginButton array|bool
 * @var $paymentHistoryItem \common\models\PaymentTransactions
 * @var $sendMailForm \frontend\models\virtual\SendMailForm
 * @var $addFavorite \frontend\models\virtual\AddFavorite
 * @var $context \yii\web\Controller|\frontend\modules\desktop\components\behaviors\RenderLayout
 */
$context = $this->context;
$this->title = 'Оплата услуги «' . $paymentHistoryItem->service->name . '»';
$this->params['header'] = 'Квитанция об оплате';
$context->getBreadcrumbsLayout()
		->appendBreadcrumb('Все платежи', '/payments')
		->appendBreadcrumb('История платежей', '/payments/history')
		->appendBreadcrumb($this->title);
use common\components\services\Helper;
?>
<div class="-mobile-padding">
	<?= $this->render('_historyItem', ['paymentHistoryItem' => $paymentHistoryItem, 'sendMailForm' => $sendMailForm])?>
	<script id="addFavoriteTemplate" type="text/template">
		<?= $this->render('//partial/site/addFavoriteForm', ['addFavorite' => $addFavorite]) ?>
	</script>
	<script id="alertTemplate" type="text/template">
		<div class="popup-alert"><div class="{{#if success}}success-message{{else}}error-message{{/if}}">{{text}}</div>
	</script>
</div>

<?php if ($userShowLoginButton): ?>
	<div class="auth-by-click -mobile-padding">
		<h2>Хотите ежемесячно быть в курсе выставленных счетов по данной услуге,
			посмотреть историю платежей или добавить платеж в избранное?
		</h2>
		<div class="auth-by-click_button-content">
			<?=\common\helpers\Html::a(\common\helpers\Html::mtsButton('Войдите', ['class' => 'auth-by-click_button']), '/site/login-by-click')?>
			<h5>Вход будет выполнен под номером <?=Helper::formatPhone($userShowLoginButton['phone'], Helper::FORMAT_SIMPLE)?></h5>
		</div>
	</div>
<?php endif; ?>
