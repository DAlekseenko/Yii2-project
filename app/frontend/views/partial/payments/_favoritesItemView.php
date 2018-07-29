<?php
/**
 * @var $model \common\models\PaymentTransactionsSearch
 */
use common\helpers\Html;
use yii\helpers\Url;
?>
<div class="mts-list-view_cell -service-name">
	<h3 class="-pb3">
		<?= Html::a(htmlspecialchars($model->name), Url::to(['/payments/pay', 'id' => $model->service_id, 'favId' => $model->id]), [
			'class' => '-dashed',
			'title' => 'Повторить платеж',
		]) ?>
	</h3>
	<h5><?= $model->service->category->name ?> / <?= $model->service->name ?></h5>
</div>
<div class="mts-list-view_cell -action -update">
	<?= Html::a('', Url::to(['/payments/update-favorite', 'id' => $model->id]), [
		'class' => 'edit-gray -with-hover',
		'title' => 'Изменить',
	]) ?>
</div>
<div class="mts-list-view_cell -action">
	<?= Html::a('', '/api/payments/del-favorite?id=' . $model->id, ['class' => 'star-red --confirm', 'title' => 'Удалить']) ?>
</div>