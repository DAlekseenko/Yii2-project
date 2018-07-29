<?php
use common\models\Locations;
?>
<div class="location">
	<div id="locationNameString" class="location_name"><?=implode(', ', Locations::getCurrentLocation()->getLocationPath())?></div>
	<a href="/site/location-select" id="locationChangeLink" data-no-location-detect="<?=!Locations::isDetectLocation()?>" data-location-set-link="/api/locations/set-location">Изменить</a>
</div>