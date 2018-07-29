<?php

/* @var $this yii\web\View */
/* @var $roots common\models\Categories[] */
/* @var array $children*/
$this->title = 'МТС Деньги - Все платежи';
$this->params['header'] = 'Платежи';
?>
<div class="-mobile-padding">
	<?= $this->render('/partial/searchForm'); ?>

	<main id="searchResult" class="-mt30">
		<?=$this->render('//partial/payments/categoriesMain', ['roots' => $roots, 'children' => $children])?>
	</main>
</div>