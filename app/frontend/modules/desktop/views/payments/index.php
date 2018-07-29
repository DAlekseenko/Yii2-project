<?php

/* @var $this yii\web\View */
/* @var $roots common\models\Categories[] */
/* @var array $children*/
$this->title = 'МТС Деньги - Все платежи';
$this->params['header'] = 'Платежи';
?>

<?= $this->render('/partial/searchForm'); ?>

<main id="searchResult" class="-mt30">
	<?=$this->render('//partial/payments/categoriesMain.php', ['roots' => $roots, 'children' => $children])?>
</main>