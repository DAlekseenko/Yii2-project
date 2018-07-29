<div class="user-info_param">
	<div class="user-info_param-header">
		Номер телефона
	</div>
	<h3>
		<?= \common\components\services\Helper::formatPhone(Yii::$app->user->identity->phone) ?>
	</h3>
</div>