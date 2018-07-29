<div class="user-info_param">
	<div class="user-info_param-header">
		Баланс<span id="refreshBalance" class="roller -balance-refresh"></span>
	</div>
	<h3 class="user-info_param-balance" id="balanceContent">
		<span id="balance"><?= \common\components\services\Helper::sumFormat(Yii::$app->user->identity->getBalance())?></span> <?= GLOBAL_CURRENCY ?>
	</h3>
</div>