<header id="topPart" class="top-part">
	<a href="http://mts.by" class="top-part_mts-logo">
		<img class="top-part_mts-logo-img" src="/img/mts-logo-print.png" alt="МТС">
	</a>
	<a href="<?=\yii\helpers\Url::to('/')?>" class="top-part_mts-money-logo"><h2 style="font-size: 18px;">МТС Деньги</h2></a>
	<div id="mainMenuToggle" class="top-part_menu-handler"></div>
</header>
<?= $this->render('/layouts/_menu.php')?>