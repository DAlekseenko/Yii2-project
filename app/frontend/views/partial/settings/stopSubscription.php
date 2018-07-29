<?php
use common\helpers\Html;

?>
<h3 class="stop-subscription_result"><strong>Ваш запрос на удаление услуги «МТС Деньги» принят, ожидайте подтверждение по SMS</strong></h3>
<h3><strong>Внимание!</strong></h3>
<p>Вы действительно хотите удалить услугу «МТС&nbsp;Деньги»?</p>
<div>
	<form id="stopSubscription">
		<?= Html::mtsButton('Удалить услугу', ['class' => 'stop-subscription_button']); ?>
	</form>
	<div class="setting-error --stop--error">Произошла ошибка. Повторите попытку позже.</div>
</div>