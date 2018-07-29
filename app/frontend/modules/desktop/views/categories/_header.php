<?php
/* @var $category common\models\Categories */
?>
<header class="categories-header">
	<h2><span id="contentHeaderText"><?= $category['name'] ?></span></h2>
	<h5 class="categories-header_description"><?= $category['description'] ?: $category['description_short'] ?></h5>
</header>