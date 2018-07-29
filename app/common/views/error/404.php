<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $file string */
/* @var $line integer */
/* @var $message string */
/* @var $trace array */
/* @var $exception Exception */
$this->title = 'Запрошенная вами страница не существует';
?>
<div class="site-error">
	<h1 class="site-error_header"><?=\common\helpers\Html::encode($this->title)?></h1>
	<div class="site-error_content clearfix">
		<img class="site-error_img" src="/img/error-404.png" alt="Страница не найдена">
		<p class="site-error_p">Возможно, вы указали неправильный адрес или страница, на которую вы хотели зайти, устарела и была удалена.</p>
		<p class="site-error_p">Для того чтобы найти интересующую вас информацию, воспользуйтесь <a href="<?=\yii\helpers\Url::to('/')?>">поиском по сайту</a>.</p>
	</div>
</div>