<?php
/* @var $category common\models\Categories */
?>
<header class="categories-header -mobile-padding">
	<h2><?= $category['name'] ?></h2>
	<h5 class="categories-header_description"><?= $category['description'] ?: $category['description_short'] ?></h5>
</header>