<?php
/**
* @var $searchModel \common\models\PaymentTransactionsSearch
*/

?>
<div class="payments-history-total">
	<span id="paymentsHistoryTotal">Всего платежей: {totalCount}.</span>
	<div class="payments-history-total_count-to-page">
		Показывать по
		<?= \yii\helpers\Html::dropDownList('', $searchModel->getPageSize(), $searchModel::PAGE_SIZES, ['id' => 'historyPageSize', 'class' => 'mts-select -payments-history-counts']) ?>
		записей
	</div>
</div>