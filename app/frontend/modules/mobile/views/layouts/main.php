<?php

/** @var $this \yii\web\View */
/** @var $content string */
/** @var \yii\web\Controller | \frontend\modules\mobile\components\behaviors\RenderLayout $context */
$context = $this->context;

use yii\helpers\Html;
use frontend\modules\mobile\assets\AppAsset;
use cybercog\yii\googleanalytics\widgets\GATracking;

AppAsset::register($this);
?>
<? $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
	<meta charset="<?= Yii::$app->charset ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?= Html::csrfMetaTags() ?>
	<title><?= Html::encode($this->title) ?></title>
	<? $this->head() ?>
	<? if (defined('GA_ID')): ?>
		<?= GATracking::widget(['trackingId' => GA_ID]) ?>
	<? endif; ?>
	<? if (YII_ENV == 'prod'): ?>
	<!-- Yandex.Metrika counter --> <script type="text/javascript" > (function (d, w, c) { (w[c] = w[c] || []).push(function() { try { w.yaCounter46319439 = new Ya.Metrika({ id:46319439, clickmap:true, trackLinks:true, accurateTrackBounce:true }); } catch(e) { } }); var n = d.getElementsByTagName("script")[0], s = d.createElement("script"), f = function () { n.parentNode.insertBefore(s, n); }; s.type = "text/javascript"; s.async = true; s.src = "https://mc.yandex.ru/metrika/watch.js"; if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); } })(document, window, "yandex_metrika_callbacks"); </script> <noscript><div><img src="https://mc.yandex.ru/watch/46319439" style="position:absolute; left:-9999px;" alt="" /></div></noscript> <!-- /Yandex.Metrika counter -->
	<? endif; ?>
</head>
<body>
<? $this->beginBody() ?>
<div class="teaser_wrap" id="teaserContainer"></div>
<div class="app">
	<?= $this->render('/layouts/_topPart.php'); ?>

	<?= $context->renderTopPanel(); ?>

	<div class="panel">
		<?= $context->renderBreadcrumbs(); ?>
		<?= $context->renderHeader(); ?>

		<div id="mainContent">
			<?= $content ?>
		</div>
	</div>

	<?= $context->renderFooter(); ?>
</div>
<? $this->endBody() ?>
</body>
</html>
<? $this->endPage() ?>
