<?php
/* @var $this yii\web\View */
?>
<?php if ($this->beginCache('asideGuest')): ?>
	<div class="aside-guest">
		<div class="aside-banner">
			<img src="/img/banner-left-img.png" alt="Управление счетами и финансовыми услугами еще никогда не было таким простым!">
		</div>
		<div class="login"><h4 class="login_header">Войдите, чтобы узнать начисления</h4></div>
		<div id="loginContent" class="login">
			<?= $this->context->renderLogin()?>
		</div>
	</div>
	<?php $this->endCache(); ?>
<?php endif; ?>