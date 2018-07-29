<?php
/* @var $this yii\web\View */
/* @var \common\models\Locations[] $regions */


$this->title = 'МТС Деньги - Выбор региона';
$this->params['header'] = 'Выбор региона';
$this->context->getTopPanelLayout()->disable();
$this->context->getBreadcrumbsLayout()->appendBreadcrumb('Выбор региона');
?>
<div class="-mobile-padding">
	<?php foreach ($regions as $region): ?>
		<div class="locations-block --locations-block">
			<h3 class="locations-block_region"><a href="/" data-location-id="<?=$region['id']?>"><?=$region['name']?></a></h3>
			<div class="locations-block_cities --cities">
				<?php foreach ($region['cities'] as $city): ?>
					<div class="locations-block_city">
						<a href="/" data-location-id="<?=$city['id']?>" data-location-set-link="/api/locations/set-location?location_id=<?=$city['id']?>"><?=$city['name']?></a>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="locations-block_toggle --toggle-link-content"><a class="-dashed --toggle-show-cities">Показать еще</a></div>
		</div>
	<?php endforeach; ?>
</div>