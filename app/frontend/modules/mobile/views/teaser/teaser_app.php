<?php
/**
 * @var $id string
 * @var $isAndroid string
 */
?>
<div class="teaser -app-download">
	<div class="teaser_cell -close --close" data-id="<?= $id ?>">×</div>
	<div class="teaser_cell -mts-logo">
		<img class="teaser_logo-img" src="/img/ic_launcher.png">
		<div class="teaser_text">
			<div class="teaser_text-header">МТС Деньги</div>
			<div class="teaser_text-small">Все платежи <b>БЕЗ КОМИССИИ</b></div>
			<div class="teaser_text-small"><b>БЕСПЛАТНО</b> в <?= $isAndroid ? 'Google Play' : 'App Store' ?></div>
		</div>
	</div>
	<a class="teaser_cell -teaser_link" href="/app/download?id=<?= $id ?>">Смотреть</a>
</div>
