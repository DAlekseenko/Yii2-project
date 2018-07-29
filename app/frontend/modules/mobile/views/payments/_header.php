<?php
/** @var $service common\models\Services */
?>
<header class="categories-header -mobile-padding">
	<div class="categories-header_logo -mobile">
		<?= $service->hasImg() ? \common\components\widgets\ServiceIcon::widget(['item' => $service]) : '' ?></div>
	<h2><?= $service['name'] ?></h2>
	<div class="categories-header_description"><?= $service['description'] ?></div>
</header>