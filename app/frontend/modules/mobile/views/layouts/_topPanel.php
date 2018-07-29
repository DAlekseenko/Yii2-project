<div class="user-panel">
	<div class="user-info">
		<?=$this->render('//partial/user/phone')?>

		<?=$this->render('//partial/user/balance')?>
	</div>
	<a href="/user/recharge"><?= \common\helpers\Html::mtsButton('Пополнить с карты', ['class' => '-up-balance']) ?></a>
</div>