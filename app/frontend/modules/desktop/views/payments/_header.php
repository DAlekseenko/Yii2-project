<?php
/** @var $service common\models\Services */
?>
<header class="categories-header">
	<?php if ($service->hasImg()): ?>
		<div class="categories-header_logo"><?= \common\components\widgets\ServiceIcon::widget(['item' => $service]) ?></div>
	<?php endif; ?>
	<h2><?= $service['name'] ?></h2>
	<div class="categories-header_description"><?= $service['description'] ?></div>
</header>