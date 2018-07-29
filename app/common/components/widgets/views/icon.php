<?php
/**
 * @var \common\models\Categories|\common\models\Services $item
 * @var $options array
 * @var string $tagName
 * @var $imgOptions array
 */
use common\helpers\Html;

$src = empty($type) ? $item->getSrc() : $item->getSrc($type);
?>
<?= Html::tag($tagName, Html::img($src, $imgOptions), $options) ?>