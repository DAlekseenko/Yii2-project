<?php

/** @var $this \yii\web\View */
/** @var $content string */
/** @var \yii\web\Controller | \frontend\modules\desktop\components\behaviors\RenderLayout $context */
$context = $this->context;

use yii\helpers\Html;
use frontend\modules\desktop\assets\AppAsset;
use cybercog\yii\googleanalytics\widgets\GATracking;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
	<!-- Google Tag Manager -->
	<script>
		(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0], j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','GTM-KFLVWL');
	</script>
	<!-- End Google Tag Manager -->
	<meta charset="<?= Yii::$app->charset ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?= Html::csrfMetaTags() ?>
	<title><?= Html::encode($this->title) ?></title>
	<?php $this->head() ?>
	<!--[if (lte IE 9)]>
	<link rel="stylesheet" href="/css/frontend/desktop/ie.css">
	<![endif]-->
	<? if (defined('GA_ID')): ?>
		<?= GATracking::widget(['trackingId' => GA_ID]) ?>
	<? endif; ?>
	<? if (YII_ENV == 'prod'): ?>
		<!-- Yandex.Metrika counter --> <script type="text/javascript" > (function (d, w, c) { (w[c] = w[c] || []).push(function() { try { w.yaCounter46319439 = new Ya.Metrika({ id:46319439, clickmap:true, trackLinks:true, accurateTrackBounce:true }); } catch(e) { } }); var n = d.getElementsByTagName("script")[0], s = d.createElement("script"), f = function () { n.parentNode.insertBefore(s, n); }; s.type = "text/javascript"; s.async = true; s.src = "https://mc.yandex.ru/metrika/watch.js"; if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); } })(document, window, "yandex_metrika_callbacks"); </script> <noscript><div><img src="https://mc.yandex.ru/watch/46319439" style="position:absolute; left:-9999px;" alt="" /></div></noscript> <!-- /Yandex.Metrika counter -->
	<? endif; ?>
</head>
<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-KFLVWL" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<?php $this->beginBody() ?>

<div class="app">
	<?= $this->render('/layouts/_topPart.php'); ?>

	<div class="wrap clearfix">
		<aside class="aside">
			<?= $context->renderAside(); ?>
			<div class="user-info -without-padding"><a href="<?= \yii::$app->params['store']['os'] ?>"><img class="ussd-banner" src="/img/banner-appstore.png"></a></div>
			<div class="user-info -without-padding"><a href="<?= \yii::$app->params['store']['android'] ?>"><img class="ussd-banner" src="/img/banner-googleplay.png"></a></div>
			<div class="user-info -without-padding"><a href="/help/ussd"><img class="ussd-banner" src="/img/ussd-banner.png"></a></div>
		</aside>
		<div class="content">
			<div class="content-table">
				<? if (\yii::$app->user->isGuest && $this->context->id == 'payments' && $this->context->action->id == 'index' && !isset($_COOKIE['main_banner'])): ?>
				<div id="mainBanner" class="content-table_row">
					<div class="main-banner">
						<img class="main-banner_img" src="/img/noauth_promo_img_bg.png">
						<div class="main-banner_content-wrap">
							<div class="main-banner_content">
								<div class="main-banner_header">Платите с мобильного МТС без комиссии!</div>
								<div class="icon-row">
									<div class="icon-row_cell -img1">Выберите<br> необходимую<br> услугу</div>
									<div class="icon-row_cell -img2">Введите свои<br> данные в форму</div>
									<div class="icon-row_cell -img3">При недостаточном<br> балансе привяжите<br> банковскую карту</div>
									<div class="icon-row_cell -img4">Подтвердите платеж<br> кодом из бесплатного<br> SMS</div>
								</div>
							</div>
						</div>
						<div class="main-banner_info">Для быстрой оплаты, просмотра истории платежей и получения бесплатных<br> уведомлений о выставленных счетах <a class="--main-banner-link" href="#loginContent">Зарегистрируйтесь</a></div>
						<div class="main-banner_cross --close"></div>
					</div>
				</div>
				<? endif; ?>
				<div class="content-table_row">
					<div class="main-content_wrap">
						<?= $context->renderBreadcrumbs(); ?>
						<?= $context->renderHeader(); ?>
						<div id="mainContent">
							<?= $content ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?= $this->render('/layouts/_footer.php'); ?>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
