<?php

/**
 * @var $this yii\web\View
 * @var $document \common\models\Documents
 */

$context = $this->context;
$this->title = "МТС Деньги - {$document->title}";
$this->params['header'] = $document->title;
$context->getBreadcrumbsLayout()->appendBreadcrumb($document->title);
?>
<div class="text-rules">
	<?= $document->text ?>
</div>
