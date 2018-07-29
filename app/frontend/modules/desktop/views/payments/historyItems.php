<?php
/**
 * @var $this yii\web\View
 * @var $paymentHistoryItems \common\models\PaymentTransactions[]
 * @var $sendMailForm \frontend\models\virtual\SendMailForm
 * @var $addFavorite \frontend\models\virtual\AddFavorite
 * @var $context \yii\web\Controller|\frontend\modules\desktop\components\behaviors\RenderLayout
 */
use frontend\models\virtual\SendMailForm;

$context = $this->context;

$this->title = 'Оплата начислений';
$this->params['header'] = $this->title;
$context->getBreadcrumbsLayout()
		->appendBreadcrumb('Все платежи', '/payments')
		->appendBreadcrumb('История платежей', '/payments/history')
		->appendBreadcrumb($this->title);
?>
<div class="payment-result-content">
	<?php foreach($paymentHistoryItems as $paymentHistoryItem):?>
		<?= $this->render('_historyItem', ['paymentHistoryItem' => $paymentHistoryItem, 'sendMailForm' => new SendMailForm()])?>
	<?php endforeach;?>
</div>
<script id="addFavoriteTemplate" type="text/template">
	<?= $this->render('//partial/site/addFavoriteForm', ['addFavorite' => $addFavorite]) ?>
</script>
<script id="alertTemplate" type="text/template">
	<div class="{{#if success}}success-message{{else}}error-message{{/if}}">{{text}}</div>
</script>