<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

$userName = Yii::$app->user->identity->getUserNameFull();
?>
<div class="user-info">
	<div class="user-info_param">
		<div class="user-info_param-header">
			Здравствуйте,
		</div>
		<h3 id="userNameFull">
			<?= $userName ?: 'Уважаемый абонент'; ?>!
		</h3>
		<?php if (!$userName): ?>
			<a id="asideChangeUserInfo" class="-dotted">Пожалуйста, представьтесь</a>
		<?php endif; ?>
	</div>

	<?=$this->render('//partial/user/phone')?>

	<?=$this->render('//partial/user/balance')?>
	<a href="/user/recharge"><?= \common\helpers\Html::mtsButton('Пополнить с карты', ['class' => '-up-balance']) ?></a>
</div>
<?=$this->render('//partial/user/menu')?>