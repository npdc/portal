<?php

$data = [
	'Entry_ID'=>[
		'Short_Name'=>$this->data['dif_id'],
		'version'=>$this->data['dataset_version']
	],
	'Summary'=>[
		'Abstract'=>$this->data['summary'],
		'Purpose'=>$this->data['purpose']
	]
];
foreach([
	'title'=>'Entry_Title',
	'dataset_progress'=>'Dataset_Progress',
	'quality'=>'Quality',
	'access_constraints'=>'Access_Constraints',
	'use_constraints'=>'Use_Constraints'
] as $source=>$target){
	if(!empty($this->data[$source])){
		$data[$target] = $this->data[$source];
	}
}

foreach($this->model->getCitations($this->data['dataset_id'], $this->data['dataset_version']) as $i=>$citation){
	foreach([
		'creator'=>'Dataset_Creator', 
		'editor'=>'Dataset_Editor', 
		'title'=>'Dataset_Title',
		'series_name'=>'Dataset_Series_name', 
		'release_date'=>'Dataset_Release_Date', 
		'release_place'=>'Dataset_Release_Place',
		'publisher'=>'Dataset_Publisher', 
		'issue_identification'=>'Issue_Identification', 
		'presentation_form'=>'Presentation_Form', 
		'other'=>'Other', 
		'persistent_identifier_type'=>'Persistent_Identifier_Type', 
		'persistent_identifier_identifier'=>'Persistent_Identifier_Identifier', 
		'online_resource'=>'Online_Resource'
	] as $source=>$target){
		if(!empty($citation[$source])){
			$data['Dataset_Citation'][$i][$target] = $citation[$source];
		}
	}
}

foreach($this->model->getPersons($this->data['dataset_id'], $this->data['dataset_version']) as $i=>$person){
	$data['Personnel'][$i]['role'] = json_decode($person['role']);
	
	$personDetails = $this->personModel->getById($person['person_id']);
	foreach([
		'given_name'=>'First_Name',
		'surname'=>'Last_Name',
		'mail'=>'Email'
	] as $source=>$target){
		$data['Personnel'][$i]['Contact_Person'][$target] = $personDetails[$source];
	}
	
	$organizationDetail = $this->organizationModel->getById($person['organization_id']);
	foreach([
		'organization_address'=>'Address',
		'organization_zip'=>'Postal_Code',
		'organization_city'=>'City',
		'country_name'=>'Country'
	] as $source=>$target){
		$data['Personnel'][$i]['Contact_Person']['Contact_Address'][$target] = $organizationDetail[$source];
	}

	foreach(['personal'=>'Direct Line', 'secretariat'=>'General Line', 'mobile'=>'Mobile Phone'] as $phoneType=>$label){
		if($personDetails['phone_'.$phoneType.'_public'] === 'yes' && !empty($personDetails['phone_'.$phoneType])){
			$data['Personnel'][$i]['Contact_Person']['Phone'][] = ['Type'=>$label, 'Number'=>$personDetails['phone_'.$phoneType]];
		}
	}
}

foreach($this->model->getKeywords($this->data['dataset_id'], $this->data['dataset_version']) as $i=>$keyword){
	foreach([
		'category'=>'Category',
		'topic'=>'Topic',
		'term'=>'Term',
		'var_lvl_1'=>'Variable_Level_1',
		'var_lvl_2'=>'Variable_Level_2',
		'var_lvl_3'=>'Variable_Level_3',
		''=>'Detailed_variable'
	] as $source=>$target){
		if(!empty($keyword[$source])){
			$data['Science_Keywords'][$i][$target] = $keyword[$source];
		}
	}
}

foreach($this->model->getTopics($this->data['dataset_id'], $this->data['dataset_version']) as $i=>$topic){
	$data['ISO_Topic_Category'][$i] = substr($topic['description'], 0, strpos($topic['description'], ':'));
}

foreach($this->model->getAncillaryKeywords($this->data['dataset_id'], $this->data['dataset_version']) as $i=>$keyword){
	$data['Keyword'][$i] = $keyword['keyword'];
}

foreach($this->model->getPlatform($this->data['dataset_id'], $this->data['dataset_version']) as $i=>$platform){
	foreach([
		'category'=>'Type',
		'short_name'=>'Short_Name',
		'long_name'=>'Long_name'
	] as $source=>$target){
		if(!empty($platform[$source])){
			$data['Platform'][$i][$target] = $platform[$source];
		}
	}
	foreach($this->model->getInstrument($platform['platform_id'], $this->data['dataset_version']) as $j=>$instrument){
		foreach([
			'short_name'=>'Short_Name',
			'long_name'=>'Long_Name',
			'technique'=>'Technique',
			'number_of_sensors'=>'NumberOfSensors',
			'opterational_mode'=>'OperationalMode'
		] as $source=>$target){
			if(!empty($instrument[$source])){
				$data['Platform'][$i]['Instrument'][$j][$target] = $instrument[$source];
			}
		}
		foreach($this->model->getSensor($instrument['instrument_id'], $this->data['dataset_version']) as $k=>$sensor){
			foreach([
				'short_name'=>'Short_Name',
				'long_name'=>'Long_Name',
				'technique'=>'Technique'
			] as $source=>$target){
				if(!empty($sensor[$source])){
					$data['Platform'][$i]['Instrument'][$j]['Sensor'][$k][$target] = $sensor[$source];
				}
			}
		}
	}
}

$units = [
	's'=>'seconds',
	'i'=>'minutes',
	'h'=> 'hours',
	'd'=> 'days',
	'w'=> 'weeks',
	'm'=> 'months',
	'y'=> 'years'
];
foreach($this->model->getTemporalCoverages($this->data['dataset_id'], $this->data['dataset_version']) as $i=>$tc){
	foreach([
		'period',
		'cycle',
		'paleo',
		'ancillary'
	] as $group){
		foreach($this->model->getTemporalCoveragesGroup($group, $tc['temporal_coverage_id'], $this->data['dataset_version']) as $j=>$tcg){
			switch($group){
				case 'period':
					if($tcg['date_start'] === $tcg['date_end']) {
						$data['Temporal_Coverage'][$i]['Single_DateTime'][$j] = $tcg['date_start'];
					} else {
						$data['Temporal_Coverage'][$i]['Range_DateTime'][$j] = ['Beginning_Date_Time'=>$tcg['date_start'], 'Ending_Date_Time'=>$tcg['date_end']];
					}
				break;
				case 'cycle':
					foreach([
						'name'=>'Name',
						'date_start'=>'Start_Date',
						'date_end'=>'End_Date',
						'sampling_frequency_unit'=>'Duration_Unit',
						'sampling_frequency'=>'Duration_Value'
					] as $source=>$target){
						$data['Temporal_Coverage'][$i]['Periodic_DateTime'][$j][$target] = $source === 'sampling_frequency_unit' ? $units[$tcg[$source]] : $tcg[$source];
					}
				break;
				case 'paleo':
					$data['Temporal_Coverage'][$i]['Paleo_DateTime'][$j] = ['Paleo_Start_Date'=>$tcg['start_value'].' '.$tcg['start_unit'], 'Paleo_End_Date'=>$tcg['end_value'].' '.$tcg['end_unit']];
					foreach($this->model->getTemporalCoveragePaleoChronounit($tcg['temporal_coverage_paleo_id'], $this->data['dataset_version']) as $k=>$tp){
						$data['Temporal_Coverage'][$i]['Paleo_DateTime'][$j]['Chronostratigraphic_Unit'][$k] = $this->vocab->formatTerm('vocab_chronounit', $tp);
					}
				break;
				case 'ancillary':
					$data['Temporal_Coverage'][$i]['Temporal_Info']['Ancillary_Temporal_Keyword'][$j] = $tcg['keyword'];
				break;
			}
		}
	}
}

foreach($this->model->getSpatialCoverages($this->data['dataset_id'], $this->data['dataset_version']) as $i=>$sc){
	$wkt = substr($sc['wkt'], strrpos($sc['wkt'], '(')+1, strpos($sc['wkt'], ')')-strlen($sc['wkt']));
	$points = explode(',', $wkt);
	if(in_array($sc['type'], ['Area', 'Polygon'])){
		array_pop($points);
	}
	foreach($points as &$point){
		$point = array_combine(['Point_Longitude','Point_Latitude'], explode(' ', $point));
	}
	$data['Spatial_Coverage'][$i]['Geometry']['Coordinate_System'] = 'CARTESIAN';
	switch($sc['type']){
		case 'Point':
			$data['Spatial_Coverage'][$i]['Geometry']['Point'] = $point;
			break;
		case 'Area':
			$latMin = min($points[0]['Point_Latitude'], $points[2]['Point_Latitude']);
			$latMax = max($points[0]['Point_Latitude'], $points[2]['Point_Latitude']);
			$lonMin = min($points[0]['Point_Longitude'], $points[2]['Point_Longitude']);
			$lonMax = max($points[0]['Point_Longitude'], $points[2]['Point_Longitude']);
			$data['Spatial_Coverage'][$i]['Geometry']['Bounding_Rectangle'] = [
				'Southernmost_Latitude'=>$latMin,
				'Northernmost_Latitude'=>$latMax,
				'Westhernmost_Longitude'=>parseLon($lonMin),
				'Easthernmost_Longitude'=>parseLon($lonMax)
			];
			break;

		case 'LineString':
			$data['Spatial_Coverage'][$i]['Geometry']['Line']['Point'] = $points;
			break;
		case 'Polygon':
			$data['Spatial_Coverage'][$i]['Geometry']['Boundary']['Point'] = $points;
			break;
	}
	foreach(['depth', 'altitude'] as $source){
		$showUnit = false;
		if(!empty($sc[$source.'_min'])){
			$data['Spatial_Coverage'][$i]['Minimum_'.ucfirst($source)] = $sc[$source.'_min'];
			$showUnit = true;
		}
		if(!empty($sc[$source.'_max'])){
			$data['Spatial_Coverage'][$i]['Maxnimum_'.ucfirst($source)] = $sc[$source.'_max'];
			$showUnit = true;
		}
		if($showUnit){
			$data['Spatial_Coverage'][$i][ucfirst($source).'_Unit'] = $sc[$source.'_unit'];
		}
	}
}

foreach($this->model->getLocations($this->data['dataset_id'], $this->data['dataset_version']) as $i=>$location){
	foreach([
		'location_category'=>'Location_Category',
		'location_type'=>'Location_Type',
		'location_subregion1'=>'Location_Subregion1',
		'location_subregion2'=>'Location_Subregion2',
		'location_subregion3'=>'Location_Subregion3',
		'detailed'=>'Detailed_Location'
	] as $source=>$target){
		if(!empty($location[$source])){
			$data['Location'][$i][$target] = $location[$source];
		}
	}
	foreach($this->vocab->getIDNNodes($location['vocab_location_id']) as $node){
		$data['IDN_Node'][$node['vocab_idn_node_id']]['Short_Name'] = $node['short_name'];
		if(!empty($node['long_name'])){
			$data['IDN_Node'][$node['vocab_idn_node_id']]['Long_Name'] = $node['long_name'];
		}
	}
}

foreach($this->model->getResolution($this->data['dataset_id'], $this->data['dataset_version']) as $i=>$resolution){
	foreach([
		'latitude_resolution'=>'Latitude_Resolution',
		'longitude_resolution'=>'Longitude_Resolution',
		'hor_range'=>'Horizontal_Resolution_Range',
		'vertical_resolution'=>'Vertical_Resolution',
		'vert_range'=>'Vertical_Resolution_Range',
		'temporal_resolution'=>'Temporal_Resolution',
		'time_range'=>'Temporal_Resolution_Range'
	] as $source=>$target){
		if(!empty($resolution[$source])){
			$data['Data_Resolution'][$i][$target] = $resolution[$source];
		}
	}	
}

foreach($this->model->getProjects($this->data['dataset_id'], $this->data['dataset_version']) as $i=>$project){
	$data['project'][$i] = [
		'Short_Name'=>$project['acronym'] ?? $project['title'],
		'Long_Name'=>$project['title'],
		'Start_Date'=>$project['date_start'],
		'End_Date'=>$project['date_start']
	];
}

$data['Originating_Center'] = $this->organizationModel->getById($this->data['originating_center'])['organization_name'];

$publicationModel = new \npdc\model\Publication();
foreach($this->model->getPublications($this->data['dataset_id'], $this->data['dataset_version']) as $i=>$publication){
	$data['Reference'][$i]['Citation'] = $publicationModel->getAuthors($publication['publication_id'], $publication['publication_version'], INF).', '
		. $publication['year'].'. '
		. $publication['title'].(in_array(substr($publication['title'],-1), ['.','?']) ? '' : '.').' '
		. $publication['journal'].' '.$publication['volume'].' ('.$publication['issue'].'), '.$publication['pages'];
}

$datacenters = $this->model->getDataCenter($this->data['dataset_id'], $this->data['dataset_version']);
if(count($datacenters) === 0){
	foreach(\npdc\config::$dataCenter as $organization_id=>$persons){
		$data['Data_Center'][$organization_id] = $this->displayDataCenter($organization_id);
		foreach($persons as $person){
			$data['Data_Center'][$organization_id]['Personnel'][$person] = $this->displayDataCenterPersonnel($person);
		}
	}
} else {
	foreach($datacenters as $i=>$datacenter){
		$data['Data_Center'][$i] = $datacenter;
	}
}

$links = array_merge(
	$this->model->getLinks($this->data['dataset_id'], $this->data['dataset_version']),
	$this->model->getLinks($this->data['dataset_id'], $this->data['dataset_version'], true)
);
foreach($links as $i=>$link){
	$data['Related_URL'][$i] = [
		'Title'=>$link['title'],
		'Description'=>$link['description']
	];
	$data['Related_URL'][$i]['URL_Content_Type']['Type'] = $link['type'];
	if(!empty($link['subtype'])){
		$data['Related_URL'][$i]['URL_Content_Type']['Subtype'] = $link['subtype'];
	}
	foreach($this->model->getLinkUrls($link['dataset_link_id'], $this->data['dataset_version']) as $j=>$url){
		$data['Related_URL'][$i]['URL'] = $url['url'];
	}
}

$xml = new \npdc\lib\Dif();
$xml->parseArray($data);
$xml->output();