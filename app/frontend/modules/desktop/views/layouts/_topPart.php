<header class="top-part">
	<div class="top-part_logo">
		<a href="http://mts.by" class="top-part_mts-logo">
			<img class="top-part_mts-logo-img" src="/img/mts-logo-print.png" alt="МТС">
		</a>
		<a href="<?=\yii\helpers\Url::to('/')?>" class="top-part_mts-money-logo"><h2>МТС Деньги</h2></a>
	</div>
	<?= $this->context->renderLocation(); ?>
	<?= $this->render('/layouts/_navBar.php')?>
</header>