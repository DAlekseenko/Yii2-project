<footer class="footer">
	<div class="footer-links">
		<img src="https://dengi.mts.by/data/img/custom/paymlogos.png" style="float: right">
		<?php if (isset($_COOKIE['siteType'])): ?>
			<a class="footer-link --change-site-version" data-site-type="<?=Yii::$app->params['siteTypes']['mobile']?>">Мобильная версия сайта</a>
		<?php endif; ?>
		<a class="footer-link -online-help" href="https://help.mts.by" target="_blank">Онлайн-поддержка</a>

	</div>
	<h5 class="footer-copyright">
		©2002-<?= date('Y') ?> СООО «Мобильные ТелеСистемы». Все права защищены.<br>
		Осуществляет деятельность в области связи на основании лицензии Министерства связи и информатизации Республики Беларусь регистрационный номер №926, действительной до 30 апреля 2022, УНП 800013732.<br>
		пр-т Независимости, 95, г. Минск, 220043 Республика Беларусь
	</h5>
</footer>