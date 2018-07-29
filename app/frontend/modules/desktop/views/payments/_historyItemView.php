<?php
/**
 * @var $model \common\models\PaymentTransactionsSearch
 */
use common\components\services\Helper;

?>
<div class="mts-list-view_cell -service-name">
	<?php if ($model->service): ?>
		<h3 class="-pb3"><a class="-dashed" href="<?=\yii\helpers\Url::to(['/payments/history-item', 'id' => $model->id])?>"><?= empty($model->item_name) ? $model->service->name : $model->item_name?></a></h3>
		<h5><?= date('d.m.Y H:i', strtotime($model->date_create)) ?></h5>
	<?php else: ?>
		Устарело
	<?php endif; ?>
</div>
<h3 class="mts-list-view_cell -history-sum">
	<?= Helper::sumFormat($model->sum) . ' ' . $model->getCurrency() ?>
</h3>
<div class="mts-list-view_cell -status -color-<?=strtolower($model->status)?>">
	<?=$model->getStatusName()?>
</div>
<div class="mts-list-view_cell -action">
	<?php if ($model->service): ?>
		<a class="star-gray -with-hover --favorite-add" title="Добавить в избранное" data-transaction-id="<?=$model->getTransactionKey() ?>" data-item-name="<?=$model->item_name?>" data-pos-x="left"></a>
	<?php endif; ?>
</div>