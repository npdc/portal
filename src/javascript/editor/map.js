function loadMap(element){
	var nr = $(element).attr('id').split('_')[1];
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
	var view = new ol.View({
		center: [0, 0],
		zoom: 0,
		projection: 'EPSG:3857'
	});
	var map = new ol.Map({
		layers: [raster, vector],
		target: $(element).attr('id'),
		view: view
	});

	$(element).data('source', source);
	$(element).data('vector', vector);
	$(element).data('map', map);
	$(element).data('view', view);

	addInteraction(nr);

	$('#mapContainer_'+nr).data('source').on('addfeature', function(f){
		if($('#mapContainer_'+nr).data('feature') !== undefined){
			$('#mapContainer_'+nr).data('source').removeFeature($('#mapContainer_'+nr).data('feature'));
		}
		$('#mapContainer_'+nr).data('feature', f.feature);
		var decimalPlaces = 6;
		var c = f.feature.getGeometry().getCoordinates();
		wkt = new ol.format.WKT();
		wktString = wkt.writeFeature(f.feature, {dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
		$('[name='+mapBaseId+'wkt_'+nr+']').val(wktString);
		$('[name='+mapBaseId+'type_'+nr+']:checked').data('wkt', wktString);
		switch(getFeatureType(nr)){
			case 'Area':
				var x = [];
				var y = [];
				c[0].forEach(function(l){
					l = ol.proj.transform(l, 'EPSG:3857', 'EPSG:4326');
					x.push(l[0]);
					y.push(l[1]);
				});
				$('[name='+mapBaseId+'north_'+nr+']').val(Math.max.apply(Math,y).toFixed(decimalPlaces));
				$('[name='+mapBaseId+'south_'+nr+']').val(Math.min.apply(Math,y).toFixed(decimalPlaces));
				$('[name='+mapBaseId+'east_'+nr+']').val(Math.max.apply(Math,x).toFixed(decimalPlaces));
				$('[name='+mapBaseId+'west_'+nr+']').val(Math.min.apply(Math,x).toFixed(decimalPlaces));
				break;
			case 'Point':
				c = ol.proj.transform(c, 'EPSG:3857', 'EPSG:4326');
				$('[name='+mapBaseId+'latitude_'+nr+']').val(c[1].toFixed(decimalPlaces));
				$('[name='+mapBaseId+'longitude_'+nr+']').val(c[0].toFixed(decimalPlaces));	
				break;
				
		}
	});
	$('.map input[name='+mapBaseId+'type_'+nr+']').each(function(){
		if(!$(this).prop('checked')){
			$('#'+$(this).attr('value')).hide();
		}
	}).on('change', function(){
		$('input[name='+$(this).attr('name')+']').each(function(){
			$('#'+$(this).attr('value')).slideUp(duration);
		});
		$('#'+$(this).attr('value')).slideDown(duration);

		$('#mapContainer_'+nr).data('map').removeInteraction($('#mapContainer_'+nr).data('draw'));
		addInteraction(nr);
		createFeature(nr, false);
	});
	$('#'+mapBaseId+'Area_'+nr+' input, #'+mapBaseId+'Point_'+nr+' input').on('change', function(){
		createFeature(nr, true);
	});
	$('[name='+mapBaseId+'wkt_'+nr+']').on('change', function(){
		createFeature(nr, 'wkt');
	});
}

function addInteraction(nr) {
	var value = getFeatureType(nr);
	if (value === undefined) {
		$('#mapContainer_'+nr).on('click', function(){npdc.alert('Please select type first');});
	} else {
		$('#mapContainer_'+nr).off('click');
		var geometryFunction, maxPoints;
		if (value === 'Area') {
			value = 'LineString';
			maxPoints = 2;
			geometryFunction = function(coordinates, geometry) {
				if (!geometry) {
					geometry = new ol.geom.Polygon(null);
				}
				var start = coordinates[0];
				var end = coordinates[1];
				geometry.setCoordinates([
					[start, [start[0], end[1]], end, [end[0], start[1]], start]
				]);

				return geometry;
			};
		}
		draw = new ol.interaction.Draw({
			source: $('#mapContainer_'+nr).data('source'),
			type: /** @type {ol.geom.GeometryType} */ (value),
			geometryFunction: geometryFunction,
			maxPoints: maxPoints
		});
		$('#mapContainer_'+nr).data('map').addInteraction(draw);
		$('#mapContainer_'+nr).data('draw', draw);
	}
}

function getFeatureType(nr){
	str = $('[name='+mapBaseId+'type_'+nr+']:checked').val();
	if(str !== undefined){
		str = str.substr(mapBaseId.length).split('_')[0];
	}
	return str;
}
function createFeature(nr, fromFields){
	getFeatureType(nr);
	$('#mapContainer_'+nr).data('source').clear();
	$('#mapContainer_'+nr).removeData('feature');
	feature = undefined;
	if(fromFields === 'wkt'){
		var wkt = new ol.format.WKT();
		if($('[name='+mapBaseId+'wkt_'+nr+']').val() !== ''){
			feature = wkt.readFeature($('[name='+mapBaseId+'wkt_'+nr+']').val(),{dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
			$('[name='+mapBaseId+'type_'+nr+']:checked').prop('checked', false);
			$('[name='+mapBaseId+'type_'+nr+'][value='+mapBaseId+feature.getGeometry().getType()+'_'+nr+']').prop('checked', true);
		}
	} else if(fromFields){
		switch(getFeatureType(nr)){
			case 'Area':
				if($.isNumeric($('[name='+mapBaseId+'north_'+nr+']').val()) 
					&& $.isNumeric($('[name='+mapBaseId+'south_'+nr+']').val())
					&& $.isNumeric($('[name='+mapBaseId+'east_'+nr+']').val())
					&& $.isNumeric($('[name='+mapBaseId+'west_'+nr+']').val())
				){
					var north = parseFloat($('[name='+mapBaseId+'north_'+nr+']').val());
					var south = parseFloat($('[name='+mapBaseId+'south_'+nr+']').val());
					var east = parseFloat($('[name='+mapBaseId+'east_'+nr+']').val());
					var west = parseFloat($('[name='+mapBaseId+'west_'+nr+']').val());
					if(east < west){
						west = west - 360;
					}
					feature = new ol.Feature({
						geometry: new ol.geom.Polygon([[
							ol.proj.transform([west,north], 'EPSG:4326', 'EPSG:3857'),
							ol.proj.transform([east,north], 'EPSG:4326', 'EPSG:3857'),
							ol.proj.transform([east,south], 'EPSG:4326', 'EPSG:3857'),
							ol.proj.transform([west,south], 'EPSG:4326', 'EPSG:3857'),
							ol.proj.transform([west,north], 'EPSG:4326', 'EPSG:3857')
						]])
					});
				}
				break;
			case 'Point':
				if($.isNumeric($('[name='+mapBaseId+'latitude_'+nr+']').val()) && $.isNumeric($('[name='+mapBaseId+'longitude_'+nr+']').val())){
					var lat = parseFloat($('[name='+mapBaseId+'latitude_'+nr+']').val());
					var lng = parseFloat($('[name='+mapBaseId+'longitude_'+nr+']').val());
					feature = new ol.Feature({
						geometry: new ol.geom.Point(ol.proj.transform([lng, lat], 'EPSG:4326', 'EPSG:3857'))
					});
					c = [lng,lat];
				}
				break;
		}
	} else {
		var wkt = new ol.format.WKT();
		if($('[name='+mapBaseId+'type_'+nr+']:checked').data('wkt') !== undefined){
			feature = wkt.readFeature($('[name='+mapBaseId+'type_'+nr+']:checked').data('wkt'),{dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
			$('[name='+mapBaseId+'wkt_'+nr+']').val($('[name='+mapBaseId+'type_'+nr+']:checked').data('wkt'));
		} else {
			$('[name='+mapBaseId+'wkt_'+nr+']').val('');
		}
	}
	if(feature !== undefined){
		$('#mapContainer_'+nr).data('source').addFeature(feature);
		$('#mapContainer_'+nr).data('feature', feature);
		var zoom = ol.animation.zoom({
			duration: duration,
			resolution: /** @type {ol.Coordinate} */ ($('#mapContainer_'+nr).data('view').getResolution())
		});
		var pan = ol.animation.pan({
			duration: duration,
			source: /** @type {ol.Coordinate} */ ($('#mapContainer_'+nr).data('view').getCenter())
		});
		$('#mapContainer_'+nr).data('map').render();
		setTimeout(function(){
			$('#mapContainer_'+nr).data('map').beforeRender(pan);
			$('#mapContainer_'+nr).data('map').beforeRender(zoom);

			if($('[name='+mapBaseId+'type_'+nr+']:checked').val().split('_')[1] === 'point'){
				$('#mapContainer_'+nr).data('view').setCenter(ol.proj.transform(c, 'EPSG:4326', 'EPSG:3857'));$('#mapContainer_'+nr).data('view').setZoom(5);
			} else {
				$('#mapContainer_'+nr).data('view').fit($('#mapContainer_'+nr).data('source').getExtent(), $('#mapContainer_'+nr).data('map').getSize(), {maxZoom: 5});
			}
		}, 1);
		if(fromFields === 'wkt'){
			$($('#mapContainer_'+nr).data('source')).trigger('addfeature');
			$('[name='+mapBaseId+'type_'+nr+']:checked').trigger('change');
		}
	}
}