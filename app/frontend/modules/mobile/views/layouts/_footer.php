<?
/**
 * @var $iosLink string
 * @var $androidLink string
 */
?>
<footer class="footer">
	<div class="footer-links">
		<a class="footer-link -online-help" href="https://help.mts.by" target="_blank">Онлайн-поддержка</a>
		<a class="footer-link --change-site-version" data-site-type="<?=Yii::$app->params['siteTypes']['desktop']?>">Полная версия сайта</a>
	</div>
	<div class="store-links">
		<a class="store-link" href="<?= $iosLink ?>"><img class="store_img" src="/img/AppStore.png"></a>
		<a class="store-link" href="<?= $androidLink ?>"><img class="store_img" src="/img/GooglePlay.png"></a>
	</div>
	<img class="store_img -cards" src="https://dengi.mts.by/data/img/custom/paymlogos.png">
	<h5 class="footer-copyright">
		©2002-<?= date('Y') ?> СООО «Мобильные ТелеСистемы» <br>
		Все права защищены. <br>
		Осуществляет деятельность в области связи на основании лицензии Министерства связи и информатизации Республики Беларусь регистрационный номер №926, действительной до 30 апреля 2022, УНП 800013732.<br>
		пр-т Независимости, 95, г. Минск, 220043 Республика Беларусь
	</h5>
</footer>
