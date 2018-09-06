<?php

/**
 * Dataset model
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\model;

class Dataset{
	public $title;
	public $content;
	private $fpdo;

	/**
	 * Constructor
	 */
	public function __construct(){
		$this->fpdo = \npdc\lib\Db::getFPDO();
	}

	/**
	 * GETTERS
	 */
	
	/**
	 * Get list of datasets
	 *
	 * @param array|null $filters (Optional) filters to filter datasets by
	 * @return array List of datasets
	 */
	public function getList($filters=null){
		global $session;
		$q = $this->fpdo->from('dataset')->where('record_status', 'published');
		$q2 = $this->fpdo
			->from('dataset')
			->join('(SELECT dataset_id, MAX(dataset_version) AS dataset_version FROM dataset GROUP BY dataset_id) a USING (dataset_id, dataset_version)');
		if($session->userLevel < NPDC_ADMIN){
			$q2->leftJoin('(SELECT * FROM dataset_person WHERE person_id = '.$session->userId.' AND editor) b ON (dataset.dataset_id=b.dataset_id AND dataset_version_min<=dataset_version AND (dataset_version_max IS NULL OR dataset_version_max >= dataset_version))')
				->where('(editor OR creator='.$session->userId.')');
		}
		if(!is_null($filters)){
			foreach($filters as $filter=>$values){
				if((is_array($values) && count($values) === 0) || empty($values)){
					continue;
				}
				switch($filter){
					case 'region':
						$q->where('region', $values);
						$q2->where('region', $values);
						break;
						
					case 'period':
						//use values swapped, include all records with start date before end date of filter and end date after start date of filter
						if(!empty($values[1])){
							$q->where('date_start <= ?', $values[1]);
							$q2->where('date_start <= ?', $values[1]);
						}
						if(!empty($values[0])){
							$q->where('date_end >= ?', $values[0]);
							$q2->where('date_end >= ?', $values[0]);
						}
						break;
						
					case 'organization':
						$q->where('(dataset.dataset_id IN (SELECT DISTINCT(dataset_id) FROM dataset_person WHERE organization_id IN ('.implode(',', $values).')) OR originating_center IN ('.implode(',', $values).'))');
						$q2->where('(dataset.dataset_id IN (SELECT DISTINCT(dataset_id) FROM dataset_person WHERE organization_id IN ('.implode(',', $values).')) OR originating_center IN ('.implode(',', $values).'))');
						break;
					case 'getData':
						$download = '';
						if(in_array('direct', $values)){
							$download = "(SELECT dataset_id, dataset_version_min, dataset_version_max FROM dataset_file INNER JOIN file USING (file_id) WHERE default_access <> 'hidden')";
						}
						if(in_array('external', $values)){
							$download .= ($download === '' ? '' : ' UNION ALL ')
							. "(SELECT dataset_id, dataset_version_min, dataset_version_max FROM dataset_link INNER JOIN vocab_url_type USING (vocab_url_type_id) WHERE type = 'GET DATA')";
						}
						$download = '(SELECT dataset_id, dataset_version_min, dataset_version_max FROM ('.$download.') download GROUP BY dataset_id, dataset_version_min, dataset_version_max) dres ON dres.dataset_id = dataset.dataset_id AND dataset_version_min<=dataset_version AND (dataset_version_max IS NULL OR dataset_version_max >= dataset_version)';
						$q->join($download);
						$q2->innerJoin($download);
				}
			}
		}
		
		$published = $q->fetchAll('dataset_id');
		$final = [];
		if($session->userLevel > NPDC_PUBLIC){
			$editor = $q2->fetchAll('dataset_id');
			if(empty($filters['editorOptions']) || $filters['editorOptions'][0] === 'all'){
				foreach($published as $id=>$row){
					$row['editor'] = array_key_exists($id, $editor);
					$row['hasDraft'] = array_key_exists($id, $editor) && $editor[$id]['record_status'] === 'draft';
					$final[$row['date_start'].'_'.$row['date_end'].'_'.$id] = $row;
					unset($editor[$id]);
				}
			} 
			foreach($editor as $id=>$row){
				$row['hasDraft'] = true;
				if(isset($filters['editorOptions']) && $filters['editorOptions'][0] !== 'all'){
					if(array_key_exists($id, $published)){
						$row = $published[$id];
						$row['hasDraft'] = $editor[$id]['record_status'] === 'draft';
					}
				}
				$row['editor'] = true;
				if(empty($filters['editorOptions'])
					|| $filters['editorOptions'][0] === 'all' 
					|| $filters['editorOptions'][0] === 'edit' 
					|| ($filters['editorOptions'][0] === 'draft' && $row['hasDraft']) 
					|| ($filters['editorOptions'][0] === 'unpublished' && $row['record_status'] === 'draft')
				){
					$final[$row['date_start'].'_'.$row['date_end'].'_'.$id] = $row;
				}
			}
		} else {
			foreach($published as $id=>$row){
				$final[$row['date_start'].'_'.$row['date_end'].'_'.$id] = $row;
			}
		}
		krsort($final);
		return $final;
	}

	/**
	 * Get dataset by id
	 *
	 * @param intger $id dataset id
	 * @param integer|string $version either numeric version, or record status
	 * @return array a dataset
	 */
	public function getById($id, $version = 'published'){
		if(!is_numeric($id)){
			$return = false;
		} else {
			$return = $this->fpdo
				->from('dataset', $id)
				->where(is_numeric($version) ? 'dataset_version' : 'record_status', $version)
				->fetch();
		}
		return $return;
	}

	/**
	 * Get dataset by uuid
	 *
	 * @param string $uuid the uuid
	 * @return array a dataset
	 */
	public function getByUUID($uuid){
		return $this->fpdo->from('dataset')->where('uuid', $uuid)->fetch();
	}
	
	/**
	 * Get the publications linked to a dataset
	 *
	 * @param integer $id dataset id
	 * @param integer $version version number of dataset
	 * @param boolean $published only show published publications or also drafts
	 * @return array list of publications
	 */
	public function getPublications($id, $version, $published = true){
		$q = $this->fpdo
			->from('dataset_publication')
			->join('publication '
				. 'ON publication.publication_id=dataset_publication.publication_id '
				. 'AND publication.publication_version >= dataset_publication.publication_version_min '
				. 'AND (dataset_publication.publication_version_max IS NULL OR publication.publication_version <= dataset_publication.publication_version_max)')
			->select('publication.*, EXTRACT(year FROM date) AS year')
			->where('dataset_id = ?', $id)
			->where('dataset_version_min <= ?', $version)
			->where('(dataset_version_max IS NULL OR dataset_version_max>= ?)', $version)
			->orderBy('date DESC, publication.publication_id, '.\npdc\lib\Db::$sortByRecordStatus);
		if($published){
			$q->where('record_status', 'published');
		} else {
			$q->join('(SELECT publication_id, MAX(publication_version) publication_version FROM publication GROUP BY publication_id) AS a ON a.publication_id=publication.publication_id AND a.publication_version=publication.publication_version');
		}
		return $q->fetchAll();
	}

	/**
	 * Get the projects linked to a dataset
	 *
	 * @param integer $id dataset id
	 * @param integer $version version number of dataset
	 * @param boolean $published only show published projects or also drafts
	 * @return array list of projects
	 */
	
	public function getProjects($id, $version, $published = true){
		$q = $this->fpdo
			->from('dataset_project')
			->join('project '
				. 'ON project.project_id=dataset_project.project_id '
				. 'AND project.project_version >= dataset_project.project_version_min '
				. 'AND (dataset_project.project_version_max IS NULL OR project.project_version <= dataset_project.project_version_max)')
			->select('project.*, date_start || \' - \' || date_end period')
			->where('dataset_id = ?', $id)
			->where('dataset_version_min <= ?', $version)
			->where('(dataset_version_max IS NULL OR dataset_version_max>= ?)', $version)
			->orderBy('date_start DESC, project.project_id, '.\npdc\lib\Db::$sortByRecordStatus);
		if($published){
			$q->where('record_status', 'published');
		} else {
			$q->join('(SELECT project_id, MAX(project_version) project_version FROM project GROUP BY project_id) AS a ON a.project_id=project.project_id AND a.project_version=project.project_version');
		}
		return $q->fetchAll();
	}

	/**
	 * Get the locations of data collection
	 *
	 * @param integer $id dataset id
	 * @param integer $version dataset version
	 * @return array list of locations
	 */
	public function getLocations($id, $version){
		return $this->fpdo
			->from('location')
			->join('vocab_location')->select('vocab_location.*')
			->where('dataset_id=?', $id)
			->where('dataset_version_min <= ?', $version)
			->where('(dataset_version_max IS NULL OR dataset_version_max>= ?)', $version)
			->fetchAll();
	}

	/**
	 * Get the spatial coverages of a dataset
	 *
	 * @param integer $id dataset id
	 * @param integer $version dataset version
	 * @return array list of spatial coverages
	 */
	public function getSpatialCoverages($id, $version){
		//WKT and GEOM are kept in sync with a trigger
		return $this->fpdo
			->from('spatial_coverage')
			->where('dataset_id=?', $id)
			->where('dataset_version_min <= ?', $version)
			->where('(dataset_version_max IS NULL OR dataset_version_max>= ?)', $version)
			->fetchAll();
	}
	
	/**
	 * Get temporal coverages
	 * 
	 * @param integer $id dataset id
	 * @param integer $version dataset version
	 * @return array list of temporal coverages
	 */
	public function getTemporalCoverages($id, $version){
		return $this->fpdo
			->from('temporal_coverage')
			->where('dataset_id=?', $id)
			->where('dataset_version_min <= ?', $version)
			->where('(dataset_version_max IS NULL OR dataset_version_max>= ?)', $version)
			->fetchAll();
	}
	
	/**
	 * Get temporal coverage subgroups
	 *
	 * @param string $group subgroup to retreive
	 * @param integer $id temporal coverage id
	 * @param integer $version dataset version
	 * @return array groups of temporal coverage
	 */
	public function getTemporalCoveragesGroup($group, $id, $version){
		return $this->fpdo
			->from('temporal_coverage_'.$group)
			->where('temporal_coverage_id=?', $id)
			->where('dataset_version_min <= ?', $version)
			->where('(dataset_version_max IS NULL OR dataset_version_max>= ?)', $version)
			->fetchAll();
	}

	/**
	 * Get chronostraticgraphic units of a temporal coverage
	 *
	 * @param integer $id temporal coverage id
	 * @param integer $version dataset version
	 * @return array
	 */
	public function getTemporalCoveragePaleoChronounit($id, $version){
		return $this->fpdo
			->from('temporal_coverage_paleo_chronounit')
			->leftJoin('vocab_chronounit')->select('vocab_chronounit.*')
			->where('temporal_coverage_paleo_id=?', $id)
			->where('dataset_version_min <= ?', $version)
			->where('(dataset_version_max IS NULL OR dataset_version_max>= ?)', $version)
			->orderBy('sort')
			->fetchAll();
	}
	
	/**
	 * Get data resolution
	 *
	 * @param integer $id dataset id
	 * @param integer $version dataset version
	 * @return void
	 */
	public function getResolution($id, $version){
		return $this->fpdo
			->from('data_resolution')
			->leftJoin('vocab_res_hor')->select('vocab_res_hor.range hor_range')
			->leftJoin('vocab_res_vert')->select('vocab_res_vert.range vert_range')
			->leftJoin('vocab_res_time')->select('vocab_res_time.range time_range')
			->where('dataset_id=?', $id)
			->where('dataset_version_min <= ?', $version)
			->where('(dataset_version_max IS NULL OR dataset_version_max>= ?)', $version)
			->fetchAll();
	}
	
	/**
	 * Get platform used for data collection
	 *
	 * @param integer $id dataset id
	 * @param integer $version dataset version
	 * @param boolean $join include information from vocab_platform in the result
	 * @return array list of platforms
	 */
	public function getPlatform($id, $version, $join=true){
		$q = $this->fpdo
			->from('platform');
		if($join){
			$q->join('vocab_platform')->select('vocab_platform.*');
		}
		return $q->where('dataset_id=?', $id)
			->where('dataset_version_min <= ?', $version)
			->where('(dataset_version_max IS NULL OR dataset_version_max>= ?)', $version)
			->fetchAll();
	}
	
	/**
	 * Get instruments used on platform
	 *
	 * @param integer $id platform id
	 * @param integer $version dataset version
	 * @param boolean $join include information from vocab_instrument in the result
	 * @return array list of instruments
	 */
	public function getInstrument($id, $version, $join = true){
		$q = $this->fpdo
			->from('instrument');
		if($join){
			$q->join('vocab_instrument')->select('vocab_instrument.*');
		}
		return $q->where('platform_id=?', $id)
			->where('dataset_version_min <= ?', $version)
			->where('(dataset_version_max IS NULL OR dataset_version_max>= ?)', $version)
			->fetchAll();
	}
	
/**
	 * Get sensors used in instrument
	 *
	 * @param integer $id instrument id
	 * @param integer $version dataset version
	 * @param boolean $join include information from vocab_instrument in the result
	 * @return array list of sensors
	 */
	public function getSensor($id, $version, $join = true){
		$q =$this->fpdo
			->from('sensor');
		if($join){
			$q->join('vocab_instrument')->select('vocab_instrument.*');
		}
		return $q->where('instrument_id=?', $id)
			->where('dataset_version_min <= ?', $version)
			->where('(dataset_version_max IS NULL OR dataset_version_max>= ?)', $version)
			->fetchAll();
	}
	
	/**
	 * Get characteristics of platform, instrument or sensor
	 *
	 * @param string $type either platform, instrument or sensor
	 * @param integer $id id of $type
	 * @param integer $version dataset version
	 * @return array list of characteristics
	 */
	public function getCharacteristics($type, $id, $version){
		return $this->fpdo
			->from('characteristics')
			->where($type.'_id=?', $id)
			->where('dataset_version_min <= ?', $version)
			->where('(dataset_version_max IS NULL OR dataset_version_max>= ?)', $version)
			->fetchAll();
	}
	
	/**
	 * Get persons linked to dataset
	 * 
	 * @param integer $id dataset id
	 * @param integer $version dataset version
	 * @return array list of persons
	 */
	public function getPersons($id, $version){
		return $this->fpdo
			->from('dataset_person')
			->join('person USING(person_id)')->select('name')
			->leftJoin('organization ON dataset_person.organization_id=organization.organization_id')->select('organization_name')
			->where('dataset_id = ?', $id)
			->where('dataset_version_min <= ?', $version)
			->where('(dataset_version_max IS NULL OR dataset_version_max>= ?)', $version)
			->orderBy('sort')
			->fetchAll();
	}

	/**
	 * Generate author string from linked persons
	 *
	 * @param integer $dataset_id dataset id
	 * @param integer $dataset_version dataset version
	 * @param integer $names number of names to be displayed before 'et al'
	 * @return string formatted list of names
	 */
	public function getAuthors($dataset_id, $dataset_version, $names=2){
		$res = $this->fpdo
			->from('dataset_person')
			->leftJoin('person')->select('COALESCE(surname || \', \' || COALESCE(initials, given_name), name) AS name')
			->where('dataset_id', $dataset_id)
			->where('dataset_version_min <= ?', $dataset_version)
			->where('(dataset_version_max IS NULL OR dataset_version_max >= ?)', $dataset_version)
			->orderBy('sort')
			->fetchAll();
		$c = count($res);
		if($c === 1){
			$return = $res[0]['name'];
		} elseif ($c === 2){
			$return = $res[0]['name'].' &amp; '.$res[1]['name'];
		} else {
			$return = '';
			if(!is_numeric($names) || $names < 1 || is_nan($names)){
				$names = 1;
			}
			for($i=0;$i<min($c-1, $names);$i++){
				$return .= ($i>0 ? '; ' : '').$res[$i]['name'];
			}
			if($c <= $names+1){
				$return .= ' &amp; '.$res[$i]['name'];
			} else {
				$return .= '; et al.';
			}
		}
		return $return;
	}

	/**
	 * get the data center holding the data
	 *
	 * @param integer $id dataset id
	 * @param integer $version dataset version
	 * @param boolean $join give full organization name (otherwise only id)
	 * @return array list of organizations
	 */
	public function getDataCenter($id, $version, $join = true){
		$q = $this->fpdo
			->from('dataset_data_center');
		if($join){
			$q->join('organization')->select('organization.*');
		}
		return $q->where('dataset_id=?', $id)
			->where('dataset_version_min <= ?', $version)
			->where('(dataset_version_max IS NULL OR dataset_version_max>= ?)', $version)
			->fetchAll();
	}
	
	/**
	 * Get details of people at data center
	 *
	 * @param integer $id data center id
	 * @param integer $version dataset version
	 * @param boolean $join include full details of persons
	 * @return array list of people
	 */
	public function getDataCenterPerson($id, $version, $join = true){
		$q =$this->fpdo
			->from('dataset_data_center_person');
		if($join){
			$q->join('person')->select('person.*');
		}
		return $q->where('dataset_data_center_id=?', $id)
			->where('dataset_version_min <= ?', $version)
			->where('(dataset_version_max IS NULL OR dataset_version_max>= ?)', $version)
			->fetchAll();
	}
	
	/**
	 * Get citations linked to dataset
	 *
	 * @param integer $id dataset id
	 * @param integer $version dataset version
	 * @param string|null $type type of citation, either this or other
	 * @return array list of citations
	 */
	public function getCitations($id, $version, $type = null){
		$q = $this->fpdo
			->from('dataset_citation')
			->where('dataset_id = ?', $id)
			->where('dataset_version_min <= ?', $version)
			->where('(dataset_version_max IS NULL OR dataset_version_max>= ?)', $version);
		if(!is_null($type)){
			$q->where('type = ?', $type);
		}
		return $q->fetchAll();
	}
	
	/**
	 * Get science keywords
	 *
	 * @param integer $id dataset id
	 * @param integer $version dataset version
	 * @return array List of keywords
	 */
	public function getKeywords($id, $version){
		return $this->fpdo
			->from('dataset_keyword')
			->join('vocab_science_keyword')->select('category,topic, term, var_lvl_1, var_lvl_2, var_lvl_3')
			->where('dataset_id = ?', $id)
			->where('dataset_version_min <= ?',$version)
			->where('(dataset_version_max IS NULL OR dataset_version_max >= ?)', $version)
			->orderBy('category, coalesce(topic, \'0\'), coalesce(term, \'0\'), coalesce(var_lvl_1, \'0\'), coalesce(var_lvl_2, \'0\'), coalesce(var_lvl_3, \'0\'), coalesce(dataset_keyword.detailed_variable, \'0\')')
			->fetchAll();
	}
	
	/**
	 * Get free keywords
	 *
	 * @param integer $id dataset id
	 * @param integer $version dataset version
	 * @return array List of keywords
	 */
	public function getAncillaryKeywords($id, $version){
		return $this->fpdo
			->from('dataset_ancillary_keyword')
			->where('dataset_id = ?', $id)
			->where('dataset_version_min <= ?',$version)
			->where('(dataset_version_max IS NULL OR dataset_version_max >= ?)', $version)
			->orderBy('keyword')
			->fetchAll();
	}
	
	/**
	 * Get ISO topics
	 *
	 * @param integer $id dataset id
	 * @param integer $version dataset version
	 * @return array List of ISO topics
	 */
	public function getTopics($id, $version){
		return $this->fpdo
			->from('dataset_topic')
			->join('vocab_iso_topic_category')->select('description')
			->where('dataset_id = ?', $id)
			->where('dataset_version_min <= ?',$version)
			->where('(dataset_version_max IS NULL OR dataset_version_max >= ?)', $version)
			->orderBy('topic')
			->fetchAll();
	}
	
	/**
	 * Get links
	 *
	 * @param integer $id dataset id
	 * @param integer $version dataset version
	 * @param boolean $getData include get data links
	 * @return array list of links
	 */
	public function getLinks($id, $version, $getData = false){
		$q =$this->fpdo
			->from('dataset_link')
			->join('vocab_url_type USING(vocab_url_type_id)')->select('vocab_url_type.*')
			->where('dataset_id = ?', $id)
			->where('dataset_version_min <= ?',$version)
			->where('(dataset_version_max IS NULL OR dataset_version_max >= ?)', $version);
		if($getData){
			$q->where('vocab_url_type_id = ?', 4);
		} else {
			$q->where('vocab_url_type_id <> ?', 4);
		}
		return $q->fetchAll();
	}
	
	/**
	 * Get urls of links
	 *
	 * @param integer $id link id
	 * @param integer $version dataset version
	 * @return array list of urls
	 */
	public function getLinkUrls($id, $version){
		return $this->fpdo
			->from('dataset_link_url')
			->where('dataset_link_id = ?', $id)
			->where('dataset_version_min <= ?',$version)
			->where('(dataset_version_max IS NULL OR dataset_version_max >= ?)', $version)
			->fetchAll();
	}
	
	/**
	 * Get files related to dataset
	 *
	 * @param integer $id dataset id
	 * @param integer $version dataset version
	 * @return array list of files
	 */
	public function getFiles($id, $version){
		return $this->fpdo
			->from('dataset_file')->join('file')->select('file.*')
			->where('dataset_id = ?', $id)
			->where('dataset_version_min <= ?',$version)
			->where('(dataset_version_max IS NULL OR dataset_version_max >= ?)', $version)
			->fetchAll();
	}
	
	/**
	 * Check if person is allowed to edit record
	 *
	 * @param integer $dataset_id id of dataset
	 * @param integer $person_id id of person
	 * @return boolean user is allowed to edit
	 */
	public function isEditor($dataset_id, $person_id){
		return is_numeric($dataset_id) 
			? (
				$this->fpdo
				->from('dataset_person')
				->where('dataset_id', $dataset_id)
				->where('person_id', $person_id)
				->where('dataset_version_max IS NULL')
				->count() > 0
			) || (
				$this->fpdo
				->from('dataset')
				->where('dataset_id', $dataset_id)
				->where('creator', $person_id)
				->where('record_status IN (\'draft\', \'published\')')
				->count() > 0
			)
			: false;
	}
	
	/**
	 * Get all available versions of dataset
	 *
	 * @param integer $dataset_id dataset id
	 * @return array list of available versions with status of each version
	 */
	public function getVersions($dataset_id){
		return $this->fpdo
			->from('dataset', $dataset_id)->select(null)
			->select('dataset_version, record_status')
			->orderBy('dataset_version DESC')
			->fetchAll();
	}
	
	/**
	 * Search for datasets
	 *
	 * @param string $string String to search for
	 * @param boolean|null $summary search in summary
	 * @param array|null $exclude list of dataset ids to ignore
	 * @param boolean|null $includeDraft also search in drafts
	 * @return array list of datasets matching the filters
	 */
	public function search($string, $summary = false, $exclude = null, $includeDraft = false){
		$q = $this->fpdo
			->from('dataset')
			->select('dataset_id, date_start || \' - \' || date_end AS date')
			->select('\'Dataset\' AS content_type')
			->orderBy('date DESC');
		if(!empty($string)){
			if($summary){
				$q->where('(title '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :search1 OR dif_id '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :search2 OR summary '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :search3)', [':search1'=>$string, ':search2'=>str_replace(' ', '_', $string), ':search3'=>$string]);
			} else {
				$q->where('(title '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :search1 OR dif_id '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :search2)', [':search1'=>$string, ':search2'=>str_replace(' ', '_', $string)]);
			}
		}
		if(is_array($exclude) && count($exclude) > 0){
			$q->where('dataset_id NOT', $exclude);
		}
		if($includeDraft) {
			$q->join('(SELECT dataset_id, MAX(dataset_version) as dataset_version FROM dataset GROUP BY dataset_id) a USING (dataset_id, dataset_version)');
		} else {
			$q->where('record_status = \'published\'');	
		}
		return $q->fetchAll();
	}
	
	/**
	 * Get last status change of the dataset version
	 *
	 * @param integer $dataset_id dataset id
	 * @param integer $version dataset version
	 * @param string|null $state new state to look for
	 * @return array status change with person
	 */
	public function getLastStatusChange($dataset_id, $version, $state = null){
		$q = $this->fpdo
			->from('record_status_change')
			->join('person')->select('person.*')
			->where('dataset_id', $dataset_id)
			->where('version', $version)
			->orderBy('datetime DESC');
		if($state !== null){
			$q->where('new_state', $state);
		}
		return $q->fetch();
	}
	
	/**
	 * Get list of status changes of the dataset version
	 *
	 * @param integer $dataset_id dataset id
	 * @param integer $version dataset version
	 * @return array list of status changes with persons details
	 */
	public function getStatusChanges($dataset_id, $version){
		return $this->fpdo
			->from('record_status_change')
			->join('person')->select('person.*')
			->where('dataset_id', $dataset_id)
			->where('version', $version)
			->orderBy('datetime DESC')
			->fetchAll();
	}
	
	/**
	 * Generate metadata plain text file for use in data zip
	 *
	 * @param integer $dataset_id dataset id
	 * @return string plain text dataset description
	 */
	public function generateMeta($dataset_id){
		$data = $this->getById($dataset_id);
		$meta = '*'.$data['title']."*\r\n\r\n*Cite dataset as*\r\n\r\n";
		foreach($this->getCitations($dataset_id, $data['dataset_version']) as $citation){
			$meta .= $citation['creator']
				. ' ('.substr($citation['release_date'],0,4).').'
				. ' /'.($citation['title'] ?? $data['title']).'./'
				. (!is_null($citation['version']) ? ' ('.$citation['version'].')' : '')
				. (!is_null($citation['release_place']) ? ' '.$citation['release_place'].'.' : '')
				. (!is_null($citation['editor']) ? ' Edited by '.$citation['editor'].'.' : '')
				. (!is_null($citation['publisher']) ? ' Published by '.$citation['publisher'].'.' : '')
				. "\r\n";
		}
		$meta .= "\r\n*Use Constraints*\r\n".$data['use_constraints']."\r\n\r\n";
		$meta .= "*Quality*\r\n".$data['quality']."\r\n\r\n";

		$meta .= "*Full metadata*\r\n".getProtocol().$_SERVER['HTTP_HOST'].BASE_URL.'/'.$data['uuid'];
		return $meta;
	}

	/**
	 * SETTERS
	 */

	/**
	 * make sure empty values are null before sending to database
	 *
	 * @param array $data data to be reformatted
	 * @param string $action will record be inserted or updated
	 * @return array reformatted data
	 */
	private function parseData($data, $action){
		$fields = ['dif_id','title','summary','purpose','region','date_start','date_end','quality','access_constraints','use_constraints','dataset_progress', 'originating_center', 'dif_revision_history', 'version_description', 'product_level_id', 'collection_data_type', 'extended_metadata', 'record_status', 'creator'];
		if($action === 'insert'){
			array_push($fields, 'dataset_version');
			if(is_numeric($data['dataset_id'])){
				array_push($fields, 'dataset_id');
			}
		}
		$values = [];
		foreach($fields as $field){
			if(in_array($field, ['record_status', 'creator']) && empty($data[$field])){
				continue;
			}
			if(array_key_exists($field, $data)){
				$values[$field] = empty($data[$field]) ? null : $data[$field];
			}
		}
		return $values;
	}
	

	public function insertGeneral($data){
		$values = $this->parseData($data, 'insert');
		$r = $this->fpdo->from('dataset')->where($values)->fetch();
		if($r === false){
			$this->fpdo->insertInto('dataset', $values)->execute();
			$r = $this->fpdo->from('dataset')->where($values)->fetch();
		}
		$this->fpdo
			->update('dataset')
			->set(['uuid'=>\Lootils\Uuid\Uuid::createV5(\npdc\config::$UUIDNamespace ?? \Lootils\Uuid\Uuid::createV4(), 'dataset/'.$r['dataset_id'].'/'.$r['dataset_version'])->getUUID()])
			->where('dataset_id', $r['dataset_id'])
			->where('dataset_version', $r['dataset_version'])
			->execute();
		return $r['dataset_id'];
	}
	
	public function updateGeneral($data, $id, $version){
		$values = $this->parseData($data, 'update');
		return $this->fpdo
			->update('dataset')
			->set($values)
			->where('dataset_id', $id)
			->where('dataset_version', $version)
			->execute();
	}
	
	public function insertSpatialCoverage($data){
		//WKT and GEOM are kept in sync with a trigger
		$this->fpdo
			->insertInto('spatial_coverage', $data)
			->execute();
		return $this->fpdo
			->from('spatial_coverage')
			->where($data)
			->fetch()['spatial_coverage_id'];
	}
	
	public function updateSpatialCoverage($record, $data, $version){
		//WKT and GEOM are kept in sync with a trigger
		$oldRecord = $this->fpdo
			->from('spatial_coverage', $record)
			->fetch();
		$createNew = false;
		if($oldRecord['dataset_version_min'] != $version){
			foreach($data as $key=>$val){
				if($val != $oldRecord[$key]){
					$createNew = true;
				}
			}
		}
		if($createNew){
			$this->fpdo
				->update('spatial_coverage', ['dataset_version_max'=>$version-1], $record)
				->execute();
			$data['dataset_version_min'] = $version;
			$return = $this->insertSpatialCoverage($data);
		} else {
			$return = $this->fpdo
				->update('spatial_coverage', $data, $record)
				->execute() === 1;
		}
		return $return;
	}

	public function deleteSpatialCoverage($dataset_id, $version, $current){
		$q = $this->fpdo
			->update('spatial_coverage')
			->set('dataset_version_max', $version)
			->where('dataset_id', $dataset_id)
			->where('dataset_version_max', NULL);
		if(count($current) === 1){
			$q->where('spatial_coverage_id <> ?', $current[0]);	
		} elseif(count($current) > 1){
			$q->where('spatial_coverage_id NOT ', $current);
		}
		$q->execute();
		$this->fpdo
			->deleteFrom('spatial_coverage')
			->where('dataset_version_min > dataset_version_max')
			->execute();
	}

	public function insertResolution($data){
		//WKT and GEOM are kept in sync with a trigger
		$this->fpdo
			->insertInto('data_resolution', $data)
			->execute();
		return $this->fpdo
			->from('data_resolution')
			->where($data)
			->fetch()['data_resolution_id'];
	}
	
	public function updateResolution($record, $data, $version){
		//WKT and GEOM are kept in sync with a trigger
		$oldRecord = $this->fpdo
			->from('data_resolution', $record)
			->fetch();
		$createNew = false;
		if($oldRecord['dataset_version_min'] != $version){
			foreach($data as $key=>$val){
				if($val != $oldRecord[$key]){
					$createNew = true;
				}
			}
		}
		if($createNew){
			$this->fpdo
				->update('data_resolution', ['dataset_version_max'=>$version-1], $record)
				->execute();
			$data['dataset_version_min'] = $version;
			$return = $this->insertResolution($data);
		} else {
			$return = $this->fpdo
				->update('data_resolution', $data, $record)
				->execute() === 1;
		}
		return $return;
	}

	public function deleteResolution($dataset_id, $version, $current){
		$q = $this->fpdo
			->update('data_resolution')
			->set('dataset_version_max', $version)
			->where('dataset_id', $dataset_id)
			->where('dataset_version_max', NULL);
		if(count($current) === 1){
			$q->where('data_resolution_id <> ?', $current[0]);	
		} elseif(count($current) > 1){
			$q->where('data_resolution_id NOT ', $current);
		}
		$q->execute();
		$this->fpdo
			->deleteFrom('data_resolution')
			->where('dataset_version_min > dataset_version_max')
			->execute();
	}
	
	public function insertLocation($data){
		return \npdc\lib\Db::insertReturnId('location', $data);
	}

	public function updateLocation($record, $data, $version){
		$oldRecord = $this->fpdo
			->from('location', $record)
			->fetch();
		$createNew = false;
		if($oldRecord['dataset_version_min'] != $version){
			foreach($data as $key=>$val){
				if($val != $oldRecord[$key]){
					$createNew = true;
				}
			}
		}
		if($createNew){
			$this->fpdo
				->update('location', ['dataset_version_max'=>$version-1], $record)
				->execute();
			$data['dataset_version_min'] = $version;
			$return = $this->insertLocation($data);
		} else {
			$return = $this->fpdo
				->update('location', $data, $record)
				->execute()
					=== false
					? false
					: $record;
		}
		return $return;	
	}

	public function deleteLocation($dataset_id, $version, $current){
		$q = $this->fpdo
			->update('location')
			->set('dataset_version_max', $version)
			->where('dataset_id', $dataset_id)
			->where('dataset_version_max', NULL);
		if(count($current) > 0){
			$q->where('location_id NOT', $current);
		}
		$q->execute();
		$this->fpdo
			->deleteFrom('location')
			->where('dataset_version_min > dataset_version_max')
			->execute();
	}
	
	public function insertTemporalCoverage($data){
		return \npdc\lib\Db::insertReturnId('temporal_coverage', $data);
	}
	
	public function deleteTemporalCoverage($dataset_id, $version, $current){
		$q = $this->fpdo
			->update('temporal_coverage')
			->set('dataset_version_max', $version)
			->where('dataset_id', $dataset_id)
			->where('dataset_version_max', NULL);
		if(count($current) > 0){
			$q->where('temporal_coverage_id NOT', $current);
		}
		$q->execute();
		/*$this->fpdo
			->deleteFrom('temporal_coverage')
			->where('dataset_version_min > dataset_version_max')
			->execute();*/
	}

	
	public function insertTemporalCoveragePeriod($data){
		return \npdc\lib\Db::insertReturnId('temporal_coverage_period', $data);
	}
	
	public function updateTemporalCoveragePeriod($record, $data, $version){
		$oldRecord = $this->fpdo
			->from('temporal_coverage_period', $record)
			->fetch();
		$createNew = false;
		if($oldRecord['dataset_version_min'] != $version){
			foreach($data as $key=>$val){
				if($val != $oldRecord[$key]){
					$createNew = true;
				}
			}
		}
		if($createNew){
			$this->fpdo
				->update('temporal_coverage_period', ['dataset_version_max'=>$version-1], $record)
				->execute();
			$data['dataset_version_min'] = $version;
			$return = $this->insertTemporalCoveragePeriod($data);
		} else {
			$return = $this->fpdo
				->update('temporal_coverage_period', $data, $record)
				->execute()
					=== false
					? false
					: $record;
		}
		return $return;	
	}
	
	public function deleteTemporalCoveragePeriod($temporal_coverage_id, $version, $current){
		$q = $this->fpdo
			->update('temporal_coverage_period')
			->set('dataset_version_max', $version)
			->where('temporal_coverage_id', $temporal_coverage_id)
			->where('dataset_version_max', NULL);
		if(count($current) > 0){
			$q->where('temporal_coverage_period_id NOT', $current);
			
		}
		$q->execute();
	}
	
	public function insertTemporalCoverageCycle($data){
		return \npdc\lib\Db::insertReturnId('temporal_coverage_cycle', $data);
	}
	
	public function updateTemporalCoverageCycle($record, $data, $version){
		$oldRecord = $this->fpdo
			->from('temporal_coverage_cycle', $record)
			->fetch();
		$createNew = false;
		if($oldRecord['dataset_version_min'] != $version){
			foreach($data as $key=>$val){
				if($val != $oldRecord[$key]){
					$createNew = true;
				}
			}
		}
		if($createNew){
			$this->fpdo
				->update('temporal_coverage_cycle', ['dataset_version_max'=>$version-1], $record)
				->execute();
			$data['dataset_version_min'] = $version;
			$return = $this->insertTemporalCoverageCycle($data);
		} else {
			$return = $this->fpdo
				->update('temporal_coverage_cycle', $data, $record)
				->execute()
					=== false
					? false
					: $record;
		}
		return $return;	
	}
	
	public function deleteTemporalCoverageCycle($temporal_coverage_id, $version, $current){
		$q = $this->fpdo
			->update('temporal_coverage_cycle')
			->set('dataset_version_max', $version)
			->where('temporal_coverage_id', $temporal_coverage_id)
			->where('temporal_coverage_cycle.dataset_version_max', NULL);
		if(count($current) > 0){
			$q->where('temporal_coverage_cycle_id NOT', $current);
		}
		$q->execute();
	}
	
	public function insertTemporalCoverageAncillary($data){
		return \npdc\lib\Db::insertReturnId('temporal_coverage_ancillary', $data);
	}
	
	public function updateTemporalCoverageAncillary($record, $data, $version){
		$oldRecord = $this->fpdo
			->from('temporal_coverage_ancillary', $record)
			->fetch();
		$createNew = false;
		if($oldRecord['dataset_version_min'] != $version){
			foreach($data as $key=>$val){
				if($val != $oldRecord[$key]){
					$createNew = true;
				}
			}
		}
		if($createNew){
			$this->fpdo
				->update('temporal_coverage_ancillary', ['dataset_version_max'=>$version-1], $record)
				->execute();
			$return = $this->insertTemporalCoverageAncillary($data);
		} else {
			$return = $this->fpdo
				->update('temporal_coverage_ancillary', $data, $record)
				->execute()
					=== false
					? false
					: $record;
		}
		return $return;	
	}
	
	public function deleteTemporalCoverageAncillary($temporal_coverage_id, $version, $current){
		$q = $this->fpdo
			->update('temporal_coverage_ancillary')
			->set('dataset_version_max', $version)
			->where('temporal_coverage_id', $temporal_coverage_id)
			->where('temporal_coverage_ancillary.dataset_version_max', NULL);
		if(count($current) > 0){
			$q->where('temporal_coverage_ancillary_id NOT', $current);
		}
		$q->execute();
	}
	
	public function insertTemporalCoveragePaleo($data){
		return \npdc\lib\Db::insertReturnId('temporal_coverage_paleo', $data);
	}
	
	public function updateTemporalCoveragePaleo($record, $data, $version){
		$oldRecord = $this->fpdo
			->from('temporal_coverage_paleo', $record)
			->fetch();
		$createNew = false;
		if($oldRecord['dataset_version_min'] != $version){
			foreach($data as $key=>$val){
				if($val != $oldRecord[$key]){
					$createNew = true;
				}
			}
		}
		if($createNew){
			$this->fpdo
				->update('temporal_coverage_paleo', ['dataset_version_max'=>$version-1], $record)
				->execute();
			$return = $this->insertTemporalCoveragePaleo($data);
		} else {
			$return = $this->fpdo
				->update('temporal_coverage_paleo', $data, $record)
				->execute()
					=== false
					? false
					: $record;
		}
		return $return;	
	}
	
	public function deleteTemporalCoveragePaleo($temporal_coverage_id, $version, $current){
		$q = $this->fpdo
			->update('temporal_coverage_paleo')
			->set('dataset_version_max', $version)
			->where('temporal_coverage_id', $temporal_coverage_id)
			->where('temporal_coverage_paleo.dataset_version_max', NULL);
		if(count($current) > 0){
			$q->where('temporal_coverage_paleo_id NOT', $current);
		}
		$q->execute();
	}
	
	public function insertTemporalCoveragePaleoChronounit($data){
		return \npdc\lib\Db::insertReturnId('temporal_coverage_paleo_chronounit', $data);
	}

	public function deleteTemporalCoveragePaleoChronounit($id, $coverage, $version){
		$q = $this->fpdo
			->update('temporal_coverage_paleo_chronounit')
			->set('dataset_version_max', $version)
			->where('temporal_coverage_paleo_id', $coverage)
			->where('dataset_version_max', null)
			->where('vocab_chronounit_id', $id)
			->execute();
	}

	public function insertTopic($topic_id, $dataset_id, $dataset_version){
		return $this->fpdo
			->insertInto('dataset_topic', ['dataset_id'=>$dataset_id, 'dataset_version_min'=>$dataset_version, 'vocab_iso_topic_category_id'=>$topic_id])
			->execute();
	}

	public function deleteTopic($topic_id, $dataset_id, $dataset_version){
		$this->fpdo
			->update('dataset_topic')
			->set('dataset_version_max', $dataset_version)
			->where('vocab_iso_topic_category_id', $topic_id)
			->where('dataset_id', $dataset_id)
			->where('dataset_version_max IS NULL')
			->execute();
		return $this->fpdo
			->deleteFrom('dataset_topic')
			->where('dataset_version_min > dataset_version_max')
			->execute();
	}
	
	public function insertScienceKeyword($data){
		$r = $this->fpdo->from('dataset_keyword')->where($data)->fetch();
		if($r === false){
			$this->fpdo->insertInto('dataset_keyword', $data)->execute();
			$r = $this->fpdo->from('dataset_keyword')->where($data)->fetch();
		}
		return $r['dataset_keyword_id'];
	}
	
	public function updateScienceKeyword($record, $data, $version){
		$oldRecord = $this->fpdo
			->from('dataset_keyword',$record)
			->fetch();
		
		$createNew = false;
		foreach($data as $key=>$val){
			if($val != $oldRecord[$key]){
				$createNew = true;
			}
		}
		if($oldRecord['dataset_version_min'] === $version && $createNew){
			$return = $this->fpdo
				->update('dataset_keyword', $data, $record)
				->execute() === 1;
		} elseif($createNew){
			$this->fpdo
				->update('dataset_keyword', ['dataset_version_max'=>$version-1], $record)
				->execute();
			$return = $this->insertScienceKeyword(array_merge($data,['dataset_version_min'=>$version, 'dataset_id'=>$oldRecord['dataset_id']]));
		} else {
			$return = true;
		}
		return $return;
	}
	
	public function deleteScienceKeyword($dataset_id, $version, $current){
		$this->fpdo
			->update('dataset_keyword')
			->set('dataset_version_max', $version)
			->where('dataset_id', $dataset_id)
			->where('dataset_version_max IS NULL')
			->where('dataset_keyword_id NOT', $current)
			->execute();
		return $this->fpdo
			->deleteFrom('dataset_keyword')
			->where('dataset_version_min > dataset_version_max')
			->execute();
	}
	
	public function insertAncillaryKeyword($word, $id, $version){
		return $this->fpdo
			->insertInto('dataset_ancillary_keyword')
			->values(['dataset_id'=>$id,
				'dataset_version_min'=>$version,
				'keyword'=>$word])
			->execute();
	}
	
	public function deleteAncillaryKeyword($word, $id, $version){
		$this->fpdo
			->update('dataset_ancillary_keyword')
			->set('dataset_version_max', $version)
			->where('dataset_id', $id)
			->where('keyword', $word)
			->where('dataset_version_max', null)
			->execute();
		$this->fpdo
			->deleteFrom('dataset_ancillary_keyword')
			->where('dataset_version_max < dataset_version_min')
			->execute();
	}
	
	public function insertPerson($data){
		return $this->fpdo
			->insertInto('dataset_person', $data)
			->execute();
	}
	
	public function updatePerson($record, $data, $version){
		$oldRecord = $this->fpdo
			->from('dataset_person')
			->where($record)
			->fetch();
		
		$createNew = false;
		$updateEditor = false;
		foreach($data as $key=>$val){
			if($val != $oldRecord[$key]){
				if($key === 'editor'){
					$updateEditor = true;
				} else {
					$createNew = true;
				}
			}
		}
		if($oldRecord['dataset_version_min'] === $version && ($createNew || $updateEditor)){
			$return = $this->fpdo
				->update('dataset_person')
				->set($data)
				->where($record)
				->execute() === 1;
		} elseif($createNew){
			$this->fpdo
				->update('dataset_person')
				->set('dataset_version_max', $version-1)
				->where($record)
				->execute();
			$return = $this->insertPerson(array_merge($data, $record, ['dataset_version_min'=>$version, 'dataset_id'=>$oldRecord['dataset_id']]));
		} elseif($updateEditor) {
			$return = $this->fpdo
				->update('dataset_person')
				->set('editor', $data['editor'])
				->where($record)
				->execute();
		} else {
			$return = true;
		}
		return $return;
	}
	
	public function deletePerson($dataset_id, $version, $currentPersons){
		$q = $this->fpdo
			->update('dataset_person')
			->set('dataset_version_max', $version)
			->where('dataset_id', $dataset_id)
			->where('dataset_version_max', null);
		if(count($currentPersons) > 0){
			foreach($currentPersons as $person){
				if(!is_numeric($person)){
					die('Something went wrong! (e_deletePerson '.$person.')');
				}
			}
			if(count($currentPersons) === 1){
				$q->where('person_id <> ?',$currentPersons[0]);
			} else {
				$q->where('person_id NOT IN ('.implode(',',$currentPersons).')');
			}
		}
		$q->execute();
		$this->fpdo
			->deleteFrom('dataset_person')
			->where('dataset_version_max < dataset_version_min')
			->execute();
		return true;
	}
	
	public function insertDataCenter($data){
		return $this->fpdo
			->insertInto('dataset_data_center', $data)
			->execute();
	}
	
	public function updateDataCenter($record, $data, $version){
		$oldRecord = $this->fpdo
			->from('dataset_data_center', $record)
			->fetch();
		
		$createNew = false;
		foreach($data as $key=>$val){
			if($val != $oldRecord[$key]){
				$createNew = true;
			}
		}
		if($oldRecord['dataset_version_min'] === $version && $createNew){
			$return = $this->fpdo
				->update('dataset_data_center', $data, $record)
				->execute() === 1;
		} elseif($createNew){
			$this->fpdo
				->update('dataset_data_center', ['dataset_version_max'=>$version-1], $record)
				->execute();
			$return = $this->insertDataCenter(array_merge($data, ['dataset_version_min'=>$version]));
		} else {
			$return = true;
		}
		return $return;
	}
	
	public function deleteDataCenter($dataset_id, $version, $currentDataCenters){
		$q = $this->fpdo
			->update('dataset_data_center')
			->set('dataset_version_max', $version)
			->where('dataset_id', $dataset_id)
			->where('dataset_version_max', null);
		if(count($currentDataCenters) > 0){
			foreach($currentDataCenters as $dataset_data_center){
				if(!is_numeric($dataset_data_center)){
					die('Something went wrong! (e_deleteDataCenter '.$dataset_data_center.')');
				}
			}
			if(count($currentDataCenters) === 1){
				$q->where('dataset_data_center_id <> ?',$currentDataCenters[0]);
			} else {
				$q->where('dataset_data_center_id NOT IN ('.implode(',',$currentDataCenters).')');
			}
		}
		$q->execute();
		$this->fpdo
			->deleteFrom('dataset_data_center')
			->where('dataset_version_max < dataset_version_min')
			->execute();
		return true;
	}
	
	public function insertDataCenterPerson($data){
		return $this->fpdo
			->insertInto('dataset_data_center_person', $data)
			->execute();
	}
	
	public function deleteDataCenterPerson($person_id, $dataCenterId, $version){
		$q = $this->fpdo
			->update('dataset_data_center_person')
			->set('dataset_version_max', $version)
			->where('dataset_data_center_id', $dataCenterId)
			->where('person_id', $person_id)
			->where('dataset_version_max', null)
			->execute();
		$this->fpdo
			->deleteFrom('dataset_data_center_person')
			->where('dataset_version_max < dataset_version_min')
			->execute();
	}
	
	public function insertProject($data){
		$this->fpdo
			->insertInto('dataset_project')
			->values($data)
			->execute();
	}

	public function deleteProject($dataset_id, $version, $currentProjects){
		$q = $this->fpdo
			->update('dataset_project')
			->set('dataset_version_max', $version)
			->where('dataset_id', $dataset_id)
			->where('dataset_version_max', null);
		if(count($currentProjects) > 0){
			if(count($currentProjects) === 1){
				$q->where('project_id <> ?',$currentProjects[0]);
			} else {
				$q->where('project_id NOT', $currentProjects);
			}
		}
		$q->execute();
		/*\npdc\lib\Db::executeQuery('UPDATE dataset_project SET project_version_max=project_version FROM project WHERE dataset_version_max IS NOT NULL AND project.project_id=dataset_project.project_id AND record_status=\'published\'');		
		\npdc\lib\Db::executeQuery('UPDATE dataset_project SET dataset_version_max=dataset_version FROM dataset WHERE project_version_max IS NOT NULL AND dataset.dataset_id=dataset_project.dataset_id AND record_status=\'published\'');
		$this->fpdo
			->deleteFrom('dataset_project')
			->where('dataset_version_max < dataset_version_min')
			->execute();*/
		return true;
	}

	public function insertPublication($data){
		$this->fpdo
			->insertInto('dataset_publication')
			->values($data)
			->execute();
	}

	public function deletePublication($dataset_id, $version, $currentPublications){
		$q = $this->fpdo
			->update('dataset_publication')
			->set('dataset_version_max', $version)
			->where('dataset_id', $dataset_id)
			->where('dataset_version_max', null);
		if(count($currentPublications) > 0){
			if(count($currentPublications) === 1){
				$q->where('publication_id <> ?',$currentPublications[0]);
			} else {
				$q->where('publication_id NOT', $currentPublications);
			}
		}
		$q->execute();
		/*\npdc\lib\Db::executeQuery('UPDATE dataset_publication SET publication_version_max=publication_version FROM publication WHERE dataset_version_max IS NOT NULL AND publication.publication_id=dataset_publication.publication_id AND record_status=\'published\'');		
		\npdc\lib\Db::executeQuery('UPDATE dataset_publication SET dataset_version_max=dataset_version FROM dataset WHERE publication_version_max IS NOT NULL AND dataset.dataset_id=dataset_publication.dataset_id AND record_status=\'published\'');
		$this->fpdo
			->deleteFrom('dataset_publication')
			->where('dataset_version_max < dataset_version_min')
			->execute();*/
		return true;
	}

	public function insertCitation($data){
		return $this->fpdo
			->insertInto('dataset_citation', $data)
			->execute();
	}
	
	public function updateCitation($record, $data, $version){
		$oldRecord = $this->fpdo
			->from('dataset_citation', $record)
			->fetch();
		
		$createNew = false;
		foreach($data as $key=>$val){
			if($val != $oldRecord[$key]){
				$createNew = true;
			}
		}
		if($oldRecord['dataset_version_min'] === $version && $createNew){
			$return = $this->fpdo
				->update('dataset_citation', $data, $record)
				->execute() === 1;
		} elseif($createNew){
			$this->fpdo
				->update('dataset_citation', ['dataset_version_max'=>$version-1], $record)
				->execute();
			$return = $this->insertCitation(array_merge($data, ['dataset_version_min'=>$version, 'dataset_id'=>$oldRecord['dataset_id']]));
		} else {
			$return = true;
		}
		return $return;
	}
	
	public function deleteCitation($dataset_id, $version, $currentCitations, $type = null){
		$q = $this->fpdo
			->update('dataset_citation')
			->set('dataset_version_max', $version)
			->where('dataset_id', $dataset_id)
			->where('dataset_version_max', null);
		if(count($currentCitations) > 0){
			foreach($currentCitations as $citation){
				if(!is_numeric($citation)){
					die('Something went wrong! (e_deleteCitation '.$citation.')');
				}
			}
			if(count($currentCitations) === 1){
				$q->where('dataset_citation_id <> ?',$currentCitations[0]);
			} else {
				$q->where('dataset_citation_id NOT IN ('.implode(',',$currentCitations).')');
			}
		}
		if(!is_null($type)){
			$q->where('type = ?', $type);
		}
		$q->execute();
		$this->fpdo
			->deleteFrom('dataset_citation')
			->where('dataset_version_max < dataset_version_min')
			->execute();
		return true;
	}

	public function insertPlatform($data){
		return $this->fpdo
			->insertInto('platform', $data)
			->execute();
	}
	
	public function updatePlatform($record, $data, $version){
		$oldRecord = $this->fpdo
			->from('platform', $record)
			->fetch();
		
		$createNew = false;
		foreach($data as $key=>$val){
			if($val != $oldRecord[$key]){
				$createNew = true;
			}
		}
		if($oldRecord['dataset_version_min'] === $version && $createNew){
			$return = $this->fpdo
				->update('platform', $data, $record)
				->execute() === 1;
		} elseif($createNew){
			$this->fpdo
				->update('platform', ['dataset_version_max'=>$version-1], $record)
				->execute();
			$instruments = $this->getInstrument($oldRecord['platform_id'], $version, false);
			$return = $this->insertPlatform(array_merge($data, ['dataset_version_min'=>$version, 'dataset_id'=>$oldRecord['dataset_id']]));
			foreach($instruments as $instrument){
				$sensors = $this->getSensor($instrument['instrument_id'], $version, false);
				
				$instrument['dataset_version_min'] = $version;
				$instrument['old_instrument_id'] = $instrument['instrument_id'];
				$instrument['platform_id'] = $return;
				unset($instrument['instrument_id']);
				$i = $this->insertInstrument($instrument);
				foreach($sensors as $sensor){
					$sensor['dataset_version_min'] = $version;
					$sensor['old_sensor_id'] = $sensor['sensor_id'];
					$sensor['instrument_id'] = $i;
					unset($sensor['sensor_id']);
					$this->insertSensor($sensor);	
				}
			}
		} else {
			$return = true;
		}
		return $return;
	}
	
	public function deletePlatform($dataset_id, $version, $currentPlatforms){
		$q = $this->fpdo
			->update('platform')
			->set('dataset_version_max', $version)
			->where('dataset_id', $dataset_id)
			->where('dataset_version_max', null);
		if(count($currentPlatforms) > 0){
			foreach($currentPlatforms as $platform){
				if(!is_numeric($platform)){
					die('Something went wrong! (e_deletePlatform '.$platform.')');
				}
			}
			if(count($currentPlatforms) === 1){
				$q->where('platform_id <> ?',$currentPlatforms[0]);
			} else {
				$q->where('platform_id NOT IN ('.implode(',',$currentPlatforms).')');
			}
		}
		$q->execute();
//		$this->fpdo
//			->deleteFrom('platform')
//			->where('dataset_version_max < dataset_version_min')
//			->execute();
		return true;
	}

	public function insertInstrument($data){
		return $this->fpdo
			->insertInto('instrument', $data)
			->execute();
	}
	
	public function updateInstrument($record, $data, $version){
		$oldRecord = $this->fpdo
			->from('instrument', $record)
			->fetch();
		
		$createNew = false;
		foreach($data as $key=>$val){
			if($val != $oldRecord[$key]){
				$createNew = true;
			}
		}
		if($oldRecord['dataset_version_min'] === $version && $createNew){
			$return = $this->fpdo
				->update('instrument', $data, $record)
				->execute() === 1;
		} elseif($createNew){
			$this->fpdo
				->update('instrument', ['dataset_version_max'=>$version-1], $record)
				->execute();
			$sensors = $this->getSensor($oldRecord['instrument_id'], $version, false);
			$return = $this->insertInstrument(array_merge($data, ['dataset_version_min'=>$version]));
			if(count($sensors) > 0){
				foreach($sensors as $sensor){
					$sensor['dataset_version_min'] = $version;
					$sensor['old_sensor_id'] = $sensor['sensor_id'];
					$sensor['instrument_id'] = $return;
					unset($sensor['sensor_id']);
					$this->insertSensor($sensor);
				}
			}
		} else {
			$return = true;
		}
		return $return;
	}
	
	public function deleteInstrument($platform_id, $version, $currentInstruments){
		$q = $this->fpdo
			->update('instrument')
			->set('dataset_version_max', $version)
			->where('platform_id', $platform_id)
			->where('dataset_version_max', null);
		if(count($currentInstruments) > 0){
			foreach($currentInstruments as $instrument){
				if(!is_numeric($instrument)){
					die('Something went wrong! (e_deleteInstrument '.$instrument.')');
				}
			}
			if(count($currentInstruments) === 1){
				$q->where('instrument_id <> ?',$currentInstruments[0])
					->where('old_instrument_id <> ?',$currentInstruments[0]);
			} else {
				$q->where('instrument_id NOT IN ('.implode(',',$currentInstruments).')')
					->where('old_instrument_id NOT IN ('.implode(',',$currentInstruments).')');
			}
		}
		$q->execute();
//		$this->fpdo
//			->deleteFrom('instrument')
//			->where('dataset_version_max < dataset_version_min')
//			->execute();
		return true;
	}

	public function insertSensor($data){
		return $this->fpdo
			->insertInto('sensor', $data)
			->execute();
	}
	
	public function updateSensor($record, $data, $version){
		$oldRecord = $this->fpdo
			->from('sensor', $record)
			->fetch();
		
		$createNew = false;
		foreach($data as $key=>$val){
			if($val != $oldRecord[$key]){
				$createNew = true;
			}
		}
		if($oldRecord['dataset_version_min'] === $version && $createNew){
			$return = $this->fpdo
				->update('sensor', $data, $record)
				->execute() === 1;
		} elseif($createNew){
			$this->fpdo
				->update('sensor', ['dataset_version_max'=>$version-1], $record)
				->execute();
			$return = $this->insertSensor(array_merge($data, ['dataset_version_min'=>$version]));
		} else {
			$return = true;
		}
		return $return;
	}
	
	public function deleteSensor($instrument_id, $version, $currentSensors){
		$q = $this->fpdo
			->update('sensor')
			->set('dataset_version_max', $version)
			->where('instrument_id', $instrument_id)
			->where('dataset_version_max', null);
		if(count($currentSensors) > 0){
			foreach($currentSensors as $sensor){
				if(!is_numeric($sensor)){
					die('Something went wrong! (e_deleteSensor '.$sensor.')');
				}
			}
			if(count($currentSensors) === 1){
				$q->where('sensor_id <> ?',$currentSensors[0])
					->where('old_sensor_id <> ?',$currentSensors[0]);
			} else {
				$q->where('sensor_id NOT IN ('.implode(',',$currentSensors).')')
					->where('old_sensor_id NOT IN ('.implode(',',$currentSensors).')');
			}
		}
		$q->execute();
//		$this->fpdo
//			->deleteFrom('sensor')
//			->where('dataset_version_max < dataset_version_min')
//			->execute();
		return true;
	}

	public function insertCharacteristics($data){
		return $this->fpdo
			->insertInto('characteristics')
			->values($data)
			->execute();
	}
	
	public function updateCharacteristics($record_id, $data, $dataset_version){
		$oldRecord = $this->fpdo
			->from('characteristics', $record_id)
			->fetch();
		
		$createNew = false;
		foreach($data as $key=>$val){
			if($val != $oldRecord[$key]){
				$createNew = true;
			}
		}
		if($oldRecord['dataset_version_min'] === $dataset_version && $createNew){
			$return = $this->fpdo
				->update('characteristics', $data, $record_id)
				->execute() === 1;
		} elseif($createNew){
			$this->fpdo
				->update('characteristics', ['dataset_version_max'=>$dataset_version-1],$record_id)
				->execute();
			$return = $this->insertCharacteristics(array_merge($data, ['dataset_version_min'=>$dataset_version]));
		} else {
			$return = true;
		}
		return $return;
	}
	
	public function deleteCharacteristics($record, $dataset_version, $currentCharacteristics){
		list($type, $record_id) = $record;
		$q = $this->fpdo
			->update('characteristics')
			->set('dataset_version_max', $dataset_version)
			->where($type.'_id', $record_id)
			->where('dataset_version_max', null);
		if(count($currentCharacteristics) > 0){
			foreach($currentCharacteristics as $characteristic){
				if(!is_numeric($characteristic)){
					die('Something went wrong! (e_deleteCharacteristic '.$characteristic.')');
				}
			}
			if(count($currentCharacteristics) === 1){
				$q->where('characteristics_id <> ?',$currentCharacteristics[0]);
			} else {
				$q->where('characteristics_id NOT IN ('.implode(',',$currentCharacteristics).')');
			}
		}
		$q->execute();
		$this->fpdo
			->deleteFrom('characteristics')
			->where('dataset_version_max < dataset_version_min')
			->execute();
		return true;
	}
	
	public function insertLink($data){
		return $this->fpdo
			->insertInto('dataset_link', $data)
			->execute();
	}
	
	public function updateLink($record, $data, $version){
		$oldRecord = $this->fpdo
			->from('dataset_link', $record)
			->fetch();
		
		$createNew = false;
		foreach($data as $key=>$val){
			if($val != $oldRecord[$key]){
				$createNew = true;
			}
		}
		if($oldRecord['dataset_version_min'] === $version && $createNew){
			$return = $this->fpdo
				->update('dataset_link', $data, $record)
				->execute() === 1;
		} elseif($createNew){
			$this->fpdo
				->update('dataset_link', ['dataset_version_max'=>$version-1], $record)
				->execute();
			$urls = $this->getLinkUrls($oldRecord['dataset_link_id'], $version);
			$return = $this->insertLink(array_merge($data, ['dataset_version_min'=>$version, 'dataset_id'=>$oldRecord['dataset_id']]));
			if(count($urls) > 0){
				foreach($urls as $url){
					$url['dataset_version_min'] = $version;
					$url['old_dataset_link_url_id'] = $url['dataset_link_url_id'];
					$url['dataset_link_id'] = $return;
					unset($url['dataset_link_url_id']);
					$this->insertLinkUrl($url);
				}
			}
		} else {
			$return = true;
		}
		return $return;
	}
	
	public function deleteLink($dataset_id, $version, $currentLinks){
		$q = $this->fpdo
			->update('dataset_link')
			->set('dataset_version_max', $version)
			->where('dataset_id', $dataset_id)
			->where('dataset_version_max', null);
		if(count($currentLinks) > 0){
			foreach($currentLinks as $dataset_link){
				if(!is_numeric($dataset_link)){
					die('Something went wrong! (e_deleteLink '.$dataset_link.')');
				}
			}
			if(count($currentLinks) === 1){
				$q->where('dataset_link_id <> ?',$currentLinks[0]);
			} else {
				$q->where('dataset_link_id NOT IN ('.implode(',',$currentLinks).')');
			}
		}
		$q->execute();
//		$this->fpdo
//			->deleteFrom('dataset_link')
//			->where('dataset_version_max < dataset_version_min')
//			->execute();
		return true;
	}

	public function insertLinkUrl($data){
		return $this->fpdo
			->insertInto('dataset_link_url', $data)
			->execute();
	}
	
	public function updateLinkUrl($record, $data, $version){
		$oldRecord = $this->fpdo
			->from('dataset_link_url', $record)
			->fetch();
		
		$createNew = false;
		foreach($data as $key=>$val){
			if($val != $oldRecord[$key]){
				$createNew = true;
			}
		}
		if($oldRecord['dataset_version_min'] === $version && $createNew){
			$return = $this->fpdo
				->update('dataset_link_url', $data, $record)
				->execute() === 1;
		} elseif($createNew){
			$this->fpdo
				->update('dataset_link_url', ['dataset_version_max'=>$version-1], $record)
				->execute();
			$return = $this->insertLinkUrl(array_merge($data, ['dataset_version_min'=>$version]));
		} else {
			$return = true;
		}
		return $return;
	}
	
	public function deleteLinkUrl($link_id, $version, $currentLinkUrls){
		$q = $this->fpdo
			->update('dataset_link_url')
			->set('dataset_version_max', $version)
			->where('dataset_link_id', $link_id)
			->where('dataset_version_max', null);
		if(count($currentLinkUrls) > 0){
			foreach($currentLinkUrls as $dataset_link_url){
				if(!is_numeric($dataset_link_url)){
					die('Something went wrong! (e_deleteLinkUrl '.$dataset_link_url.')');
				}
			}
			if(count($currentLinkUrls) === 1){
				$q->where('dataset_link_url_id <> ?',$currentLinkUrls[0]);
				$q->where('old_dataset_link_url_id <> ?',$currentLinkUrls[0]);
			} else {
				$q->where('dataset_link_url_id NOT IN ('.implode(',',$currentLinkUrls).')');
				$q->where('old_dataset_link_url_id NOT IN ('.implode(',',$currentLinkUrls).')');
			}
		}
		$q->execute();
//		$this->fpdo
//			->deleteFrom('dataset_link_url')
//			->where('dataset_version_max < dataset_version_min')
//			->execute();
		return true;
	}
	
	public function insertFile($data){
		$this->fpdo->insertInto('dataset_file', $data)->execute();
	}
	
	public function deleteFile($dataset_id, $version, $current){
		$q = $this->fpdo
			->update('dataset_file')
			->set('dataset_version_max', $version)
			->where('dataset_id', $dataset_id)
			->where('dataset_version_max', null);
		if(count($current) > 0){
			$q->where('file_id NOT', $current);
		}
		$q->execute();
	}

	public function setStatus($dataset_id, $old, $new, $comment = null){
		$r = $this->fpdo->from('dataset')
			->where('dataset_id', $dataset_id)
			->where('record_status', $old)
			->fetch();
		if($r !== false){
			$q = $this->fpdo
				->update('dataset')
				->set('record_status', $new);
			if($new === 'published'){
				$q->set('published', date("Y-m-d H:i:s", time()));
			}
			$return = $q
				->where('dataset_id', $dataset_id)
				->where('record_status', $old)
				->execute();
			$this->fpdo->insertInto('record_status_change', ['dataset_id'=>$dataset_id, 'version'=>$r['dataset_version'], 'old_state'=>$old, 'new_state'=>$new, 'person_id'=>$_SESSION['user']['id'], 'comment'=>$comment])->execute();
		}
		return $return;
	}
}
