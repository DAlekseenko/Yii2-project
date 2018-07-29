<?php
/**
* @var $searchModel \common\models\PaymentTransactionsSearch
*/

?>
<div class="payments-history-total -mobile-padding">
	<span id="paymentsHistoryTotal">Всего: {totalCount}</span>
	<div class="payments-history-total_count-to-page">
		На странице <?= \yii\helpers\Html::dropDownList('', $searchModel->getPageSize(), $searchModel::PAGE_SIZES, ['id' => 'historyPageSize', 'class' => 'mts-select -payments-history-counts']) ?>
	</div>
</div>