<?php
/**
 * Display of location info of dataset
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */
?>

<section class="inline"><h4>Region</h4>
<p><?=$this->data['region']?></p></section>

<h4>Location</h4>
<ul>
<?php
$locations = $this->model->getLocations($this->data['dataset_id'], $this->data['dataset_version']);
foreach($locations as $location){
	echo '<li>'.$this->vocab->formatTerm('vocab_location', $location)
		. (empty($location['detailed']) ? '' : ' > '.$location['detailed'])
		. '</li>';
}
?>
</ul>
	
<?php
$spatialCoverages = $this->model->getSpatialCoverages($this->data['dataset_id'], $this->data['dataset_version']);
if(count($spatialCoverages) > 0){
	?>
	<div id="map" style="height:300px;">
		<div id="popup" class="ol-popup">
			<a href="#" id="popup-closer" class="ol-popup-closer"></a>
			<div id="popup-content"></div>
		</div>
	</div><script>
		$().ready(function(){
			var wkt = new ol.format.WKT();
			var raster = new ol.layer.Tile({
				source: new ol.source.OSM()
			});
			var source = new ol.source.Vector({wrapX: false});
			var vector = new ol.layer.Vector({
				source: source,
				style: new ol.style.Style({
					fill: new ol.style.Fill({
						color: 'rgba(198,215,235,0.5)'
					}),
					stroke: new ol.style.Stroke({
						color: '#3b6daa',
						width: 2
					}),
					image: new ol.style.Circle({
						radius: 7,
						fill: new ol.style.Fill({
							color: '#3b6daa'
						})
					})
				})
			});
			<?php

			$this->json['spatialCoverage'] = [
				'@type'=>'Place',
				'geo'=>[],
				'additionalProperty' => [
					'@type' => ["PropertyValue", "dbpedia:Coordinate_reference_system"],
					'@id' => "http://www.opengis.net/def/crs/OGC/1.3/CRS84"
				]
			];
			foreach($spatialCoverages as $spatialCoverage){
				$wkt = str_replace([' -90', ' 90'], [' -89.999999999', ' 89.999999999'], $spatialCoverage['wkt']);
				?>
				feature = wkt.readFeature("<?=$wkt?>",{dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
				<?php
				$txt = '';
				foreach(['altitude', 'depth'] as $metric){
					if(!is_null($spatialCoverage[$metric.'_min'])){
						$txt .= '<i>'.ucfirst($metric).':</i> '.$spatialCoverage[$metric.'_min'].' - '.$spatialCoverage[$metric.'_max'].' '.$spatialCoverage[$metric.'_unit'].'<br/>';
					}
				}
				$txt = '<strong>'.(empty($spatialCoverage['label']) ? ['Point'=>'Point', 'LineString'=>'Transect', 'Area'=>'Bounding Box', 'Polygon'=>'Polygon'][$spatialCoverage['type']] : $spatialCoverage['label']).'</strong><br/>'.(empty($txt) ? '<em>No additional info</em>' : $txt);
				?>
				feature.txt = '<?=$txt?>';
				source.addFeature(feature);
				<?php
				$points = explode(',', substr($spatialCoverage['wkt'], strrpos($spatialCoverage['wkt'], '(')+1, strpos($spatialCoverage['wkt'], ')')-strrpos($spatialCoverage['wkt'], '(')-1));
				$lats = [];
				$lons = [];
				foreach($points as &$point){
					$point = explode(' ', $point);
					foreach($point as &$coord){
						$coord = round($coord, 4);
					}
					$lats[] = $point[1];
					$lons[] = $point[0];
					$point = implode(',', array_reverse($point));
				}
				switch($spatialCoverage['type']){
					case 'Point':
					$point = explode(',', $points[0]);
						$this->json['spatialCoverage']['geo'][] = [
							'@type'=>'GeoCoordinates',
							'latitude'=>$point[0],
							'longitude'=>$point[1]
						];
						break;
					case 'LineString':
						$this->json['spatialCoverage']['geo'][] = [
							'@type'=>'GeoShape',
							'line'=>implode(' ', $points)
						];
						break;
					case 'Area':
						$this->json['spatialCoverage']['geo'][] = [
							'@type'=>'GeoShape',
							'box'=>min($lats).' '.min($lons).' '.max($lats).' '.max($lons)
						];
						break;
					case 'Polygon':
						$this->json['spatialCoverage']['geo'][] = [
							'@type'=>'GeoShape',
							'polygon'=>implode(' ', $points)
						];
						break;
				}
			}
			?>
			var view = new ol.View({
				center: [0, 0],
				zoom: 0,
				projection: 'EPSG:3857',
				extent: ol.proj.transformExtent([-180,-87,180,87], 'EPSG:4326', 'EPSG:3857')
			});
			var map = new ol.Map({
				layers: [raster, vector],
				target: 'map',
				view: view,
				controls: ol.control.defaults({
					attributionOptions: ({
						collapsible: false
					})
				}).extend([
					new ol.control.ScaleLine()
				])
			});
			view.fit(source.getExtent(), map.getSize(), {maxZoom: 5});
			var center = ol.proj.transform(view.getCenter(), 'EPSG:3857', 'EPSG:4326');
			//Set view to max of 87 degrees North/South for better map rendering
			if(Math.abs(center[1]) > 87){
				view.setCenter(ol.proj.transform([center[0], Math.sign(center[1])*87], 'EPSG:4326', 'EPSG:3857'));
			}
			var element = $('#popup-content');
			var closer = $('#popup-closer');
			
			var popup = new ol.Overlay({
				element: document.getElementById('popup'),
				positioning: 'bottom-center',
				stopEvent: false
			});
			
			closer.click(function() {
				popup.setPosition(undefined);
				closer.blur();
				return false;
			});

			map.addOverlay(popup);
			map.on('pointermove', function(e) {
				var pixel = map.getEventPixel(e.originalEvent);
				var hit = map.hasFeatureAtPixel(pixel);
				map.getTargetElement().style.cursor = hit ? 'pointer' : '';
			});
			
			map.on('singleclick', function(evt) {
				var feature = map.forEachFeatureAtPixel(evt.pixel, function(feature, layer){
					return feature;
				});
				if(feature !== undefined){
					if(feature.getGeometry().getType() === 'Point'){
						popup.setPosition(feature.getGeometry().getCoordinates());
						$('#popup').addClass('point');
					} else {
						var e = feature.getGeometry().getExtent();
						popup.setPosition(feature.getGeometry().getClosestPoint([(e[0]+e[2])/2, e[3]]));
						$('#popup').removeClass('point');
					}
					element.html(feature.txt);
				} else {
					popup.setPosition(undefined);
				}
			});
		});

	</script>
<?php }?>