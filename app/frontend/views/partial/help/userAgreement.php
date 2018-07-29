<?php
/**
 * @var $this yii\web\View
 * @var \common\models\Documents $document
 */
$context = $this->context;
$this->title = 'МТС Деньги - ' . $document->title;
$this->params['header'] = $document->title;
$context->getBreadcrumbsLayout()->appendBreadcrumb($document->title);
?>
<div class="text-rules">
	<?= $document->text ?>
</div>
