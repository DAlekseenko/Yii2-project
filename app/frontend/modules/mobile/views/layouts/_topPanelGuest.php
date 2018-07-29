<div class="panel -mobile-padding">
	<div class="login"><h4 class="login_header">Войдите, чтобы узнать начисления</h4></div>
	<input type="checkbox" class="mobile-login-form-controller -hidden-checkbox" id="mobile-form-binding">
	<label class="login" for="mobile-form-binding">
		<div class="mts-button mts-button-print -max-width -without-shadow --log-form-show">Войти</div>
	</label>
	<div id="loginContent" class="login">
		<?= $this->context->renderLogin()?>
	</div>
</div>
