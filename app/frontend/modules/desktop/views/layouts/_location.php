<?php
use common\models\Locations;
?>
<div class="location">
	<span id="locationNameString" class="location_name-string"><?=implode(', ', Locations::getCurrentLocation()->getLocationPath())?></span>
	<a id="locationChangeLink" class="-dotted" data-no-location-detect="<?=!Locations::isDetectLocation()?>" data-location-id-default="<?=Locations::getLocationDefault()->id?>" data-location-id="<?=Locations::getCurrentLocation()->id?>"  data-location-set-link="/api/locations/set-location">Изменить</a>
</div>