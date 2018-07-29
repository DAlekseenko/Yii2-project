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
<div class="mts-list-view_cell -history-price">
	<h3>
		<?= Helper::sumFormat($model->sum) . ' ' . $model->getCurrency() ?>
	</h3>
	<div class="-color-<?=strtolower($model->status)?>">
		<?=$model->getStatusName()?>
	</div>
</div>
<div class="mts-list-view_cell -action-favorite">
	<?php if ($model->service): ?>
		<a class="star-gray -with-hover --favorite-add" title="Добавить в избранное" data-transaction-id="<?=$model->id?>" data-item-name="<?=$model->item_name?>"></a>
	<?php endif; ?>
</div>