<?php

/**
 * Dataset model
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\model;

class Dataset{
	private $fpdo;
	private $dsql;

	/**
	 * Constructor
	 */
	public function __construct(){
		$this->fpdo = \npdc\lib\Db::getFPDO();
		$this->dsql = \npdc\lib\Db::getDSQLcon();
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
		$q = $this->dsql->dsql()->table('dataset');
		if(!is_null($filters)){
			foreach($filters as $filter=>$values){
				if((is_array($values) && count($values) === 0) || empty($values)){
					continue;
				}
				switch($filter){
					case 'region':
						$q->where('region', $values);
						break;
					case 'period':
						//use values swapped, include all records with start date before end date of filter and end date after start date of filter
						if(!empty($values[1])){
							$q->where('date_start', '<=', $values[1]);
						}
						if(!empty($values[0])){
							$q->where('date_end', '>=', $values[0]);
						}
						break;
					case 'organization':
						$q->where(
							$q->dsql()->orExpr()
								->where('originating_center', $values)
								->where('dataset_id',
									$q->dsql()->table('dataset_person')
										->field('dataset_id')
										->where(\npdc\lib\Db::joinVersion('dataset', 'dataset_person'))
										->where('organization_id', $values)
								)
						);
						break;
					case 'getData':
						$gd = $q->dsql()->orExpr();
						if(in_array('direct', $values)){
							$gd->where('dataset_id',
								$q->dsql()->table('dataset_file')
									->field('dataset_id')
									->join('file.file_id', 'file_id', 'inner')
									->where('default_access', '<>', 'hidden')
									->where(\npdc\lib\Db::joinVersion('dataset', 'dataset_file'))
							);
						}
						if(in_array('external', $values)){
							$gd->where('dataset_id',
								$q->dsql()->table('dataset_link')
									->field('dataset_id')
									->join('vocab_url_type.vocab_url_type_id', 'vocab_url_type_id', 'inner')
									->where('type', 'GET DATA')
									->where(\npdc\lib\Db::joinVersion('dataset', 'dataset_link'))
							);
						}
						$q->where($gd);
				}
			}
		}
		$q->order('(CASE WHEN date_start IS NULL THEN 0 ELSE 1 END), date_start DESC, date_end DESC');
		$q->field('dataset.*')
			->field($q->dsql()->expr('"Dataset"'), 'content_type')
			->field($q->dsql()
				->expr('CASE WHEN record_status = [] THEN TRUE ELSE FALSE END {}', ['draft', 'hasDraft'])
			);
		if($session->userLevel > NPDC_USER){
			if($session->userLevel === NPDC_ADMIN) {
				$q->field($q->dsql()->expr('TRUE {}', ['editor']))
					->where('dataset.dataset_version', 
					$q->dsql()->table(['ds2'=>'dataset'])
						->field('MAX(dataset_version)')
						->where('ds2.dataset_id=dataset.dataset_id')
				);
			} elseif($session->userLevel === NPDC_EDITOR){
				$isEditor = $q->dsql()->table('dataset_person')
				->field('dataset_id')
				->where(\npdc\lib\Db::joinVersion('dataset', 'dataset_person'))
				->where('person_id', $session->userId)
				->where('editor');
				$q->field($q->dsql()
					->expr('CASE 
						WHEN creator=[] THEN TRUE
						WHEN EXISTS([]) THEN TRUE
						ELSE FALSE 
						END {}', [$session->userId, $isEditor, 'editor']
						)
					)->where('dataset.dataset_version', 
						$q->dsql()->table(['ds2'=>'dataset'])
							->field('MAX(dataset_version)')
							->where('ds2.dataset_id=dataset.dataset_id')
							->where($q->dsql()->andExpr()//ends with false, so inverts condition to: NOT (draft & NOT editor)
								->where('record_status', 'draft')
								->where($q->dsql()
									->expr('CASE 
										WHEN creator=[] THEN FALSE
										WHEN EXISTS([]) THEN FALSE
										ELSE TRUE 
										END', [$session->userId, $isEditor]
										)
									)
							, false)
					);
			} else {
				$q->field($q->dsql()->expr('FALSE {}', ['editor']));
			}
			switch($filters['editorOptions'][0]){
				case 'all':
					break;
				case 'unpublished':
					$q->where('dataset_version', 1);
				case 'draft':
					$q->where('record_status', 'draft');
				case 'edit':
					if($session->userLevel === NPDC_EDITOR){
						$q->where(
							$q->dsql()->orExpr()
								->where('creator', $session->userId)
								->where($q->dsql()->expr('EXISTS([])', [$isEditor]))
						);
					}
					break;
			}
		} else {
			$q->where('record_status', 'published');
		}
		return $q->get();
	}

	/**
	 * Get dataset by id
	 *
	 * @param intger $id dataset id
	 * @param integer|string $version either numeric version, or record status
	 * @return array a dataset
	 */
	public function getById($id, $version = 'published'){
		return \npdc\lib\Db::get('dataset', ['dataset_id'=>$id, (is_numeric($version) ? 'dataset_version' : 'record_status')=>$version]);
	}

	/**
	 * Get dataset by uuid
	 *
	 * @param string $uuid the uuid
	 * @return array a dataset
	 */
	public function getByUUID($uuid){
		return \npdc\lib\Db::get('dataset', ['uuid'=>$uuid]);
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
		$q = $this->dsql->dsql()
			->table('dataset_publication')
			->join('publication', \npdc\lib\Db::joinVersion('publication', 'dataset_publication'), 'inner')
			->where(\npdc\lib\Db::selectVersion('dataset', $id, $version));
		$q->order($q->expr('date DESC, publication.publication_id, '.\npdc\lib\Db::$sortByRecordStatus));
		if($published){
			$q->where('record_status', 'published');
		} else {
			$q->where('publication_version',
				$q->dsql()
					->table('publication', 'a')
					->field('max(publication_version)')
					->where('a.publication_id=publication.publication_id')
				);
		}
		return $q->get();
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
		$q = $this->dsql->dsql()
			->table('dataset_project')
			->join('project', \npdc\lib\Db::joinVersion('project', 'dataset_project'), 'inner')
			->where(\npdc\lib\Db::selectVersion('dataset', $id, $version));
		$q->field('*')
			->field($q->expr('date_start || \' - \' || date_end period'))
			->order($q->expr('date_start DESC, project.project_id, '.\npdc\lib\Db::$sortByRecordStatus));
		if($published){
			$q->where('record_status', 'published');
		} else {
			$q->where('project_version',
				$q->dsql()
					->table('project', 'a')
					->field('MAX(project_version)')
					->where('a.project_id=project.project_id')
			);
		}
		return $q->get();
	}

	/**
	 * Get the locations of data collection
	 *
	 * @param integer $id dataset id
	 * @param integer $version dataset version
	 * @return array list of locations
	 */
	public function getLocations($id, $version){
		return $this->dsql->dsql()
			->table('location')
			->join('vocab_location.vocab_location_id', 'vocab_location_id', 'inner')
			->where(\npdc\lib\Db::selectVersion('dataset', $id, $version))
			->get();
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
		return $this->dsql->dsql()
			->table('spatial_coverage')
			->where(\npdc\lib\Db::selectVersion('dataset', $id, $version))
			->get();
	}
	
	/**
	 * Get temporal coverages
	 * 
	 * @param integer $id dataset id
	 * @param integer $version dataset version
	 * @return array list of temporal coverages
	 */
	public function getTemporalCoverages($id, $version){
		return $this->dsql->dsql()
			->table('temporal_coverage')
			->where(\npdc\lib\Db::selectVersion('dataset', $id, $version))
			->get();
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
		return $this->dsql->dsql()
			->table('temporal_coverage_'.$group)
			->where('temporal_coverage_id', $id)
			->where(\npdc\lib\Db::selectVersion('dataset', $version))
			->get();
	}

	/**
	 * Get chronostraticgraphic units of a temporal coverage
	 *
	 * @param integer $id temporal coverage id
	 * @param integer $version dataset version
	 * @return array
	 */
	public function getTemporalCoveragePaleoChronounit($id, $version){
		return $this->dsql->dsql()
			->table('temporal_coverage_paleo_chronounit')
			->join('vocab_chronounit.vocab_chronounit_id', 'vocab_chronounit_id', 'left')
			->where('temporal_coverage_paleo_id', $id)
			->where(\npdc\lib\Db::selectVersion('dataset', $version))
			->order('sort')
			->get();
	}
	
	/**
	 * Get data resolution
	 *
	 * @param integer $id dataset id
	 * @param integer $version dataset version
	 * @return void
	 */
	public function getResolution($id, $version){
		return $this->dsql->dsql()
			->table('data_resolution')->field('data_resolution.*')
			->join('vocab_res_hor.vocab_res_hor_id', 'vocab_res_hor_id', 'left')->field('vocab_res_hor.range','hor_range')
			->join('vocab_res_vert.vocab_res_vert_id', 'vocab_res_vert_id', 'left')->field('vocab_res_vert.range','vert_range')
			->join('vocab_res_time.vocab_res_time_id', 'vocab_res_time_id', 'left')->field('vocab_res_time.range','time_range')
			->where(\npdc\lib\Db::selectVersion('dataset', $id, $version))
			->get();
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
		$q = $this->dsql->dsql()
			->table('platform');
		if($join){
			$q->join('vocab_platform.vocab_platform_id', 'vocab_platform_id', 'inner');
		}
		return $q->where(\npdc\lib\Db::selectVersion('dataset', $id, $version))
			->get();
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
		$q = $this->dsql->dsql()
			->table('instrument');
		if($join){
			$q->join('vocab_instrument.vocab_instrument_id', 'vocab_instrument_id', 'inner');
		}
		return $q->where('platform_id', $id)
			->where(\npdc\lib\Db::selectVersion('dataset', $version))
			->get();
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
		$q =$this->dsql->dsql()
			->table('sensor');
		if($join){
			$q->join('vocab_instrument.vocab_instrument_id', 'vocab_instrument_id', 'inner');
		}
		return $q->where('instrument_id', $id)
			->where(\npdc\lib\Db::selectVersion('dataset', $version))
			->get();
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
		return $this->dsql->dsql()
			->table('characteristics')
			->where($type.'_id', $id)
			->where(\npdc\lib\Db::selectVersion('dataset', $version))
			->get();
	}
	
	/**
	 * Get persons linked to dataset
	 * 
	 * @param integer $id dataset id
	 * @param integer $version dataset version
	 * @return array list of persons
	 */
	public function getPersons($id, $version){
		return $this->dsql->dsql()
			->table('dataset_person')->field('dataset_person.*')
			->join('person.person_id', 'person_id', 'inner')->field('name')
			->join('organization.organization_id', 'organization_id', 'left')->field('organization_name')
			->where(\npdc\lib\Db::selectVersion('dataset', $id, $version))
			->order('sort')
			->get();
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
		$q = $this->dsql->dsql()
			->table('dataset_person');
		$res = $q->join('person.person_id', 'person_id', 'left')->field($q->expr('COALESCE(surname || \', \' || COALESCE(initials, given_name), name)'), 'name')
			->where(\npdc\lib\Db::selectVersion('dataset', $dataset_id, $dataset_version))
			->order('sort')
			->get();
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
		$q = $this->dsql->dsql()
			->table('dataset_data_center');
		if($join){
			$q->join('organization.organization_id', 'organization_id', 'inner');
		}
		return $q->where(\npdc\lib\Db::selectVersion('dataset', $id, $version))
			->get();
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
		$q =$this->dsql
			->table('dataset_data_center_person');
		if($join){
			$q->join('person.person_person_id', 'person_id', 'inner');
		}
		return $q->where('dataset_data_center_id', $id)
			->where(\npdc\lib\Db::selectVersion('dataset', $version))
			->get();
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
		$q = $this->dsql->dsql()
			->table('dataset_citation')
			->where(\npdc\lib\Db::selectVersion('dataset', $id, $version));
		if(!is_null($type)){
			$q->where('type', $type);
		}
		return $q->get();
	}
	
	/**
	 * Get related datasets
	 *
	 * @param integer $id dataset id
	 * @param integer $version dataset version
	 * @return array list of related datasets
	 */
	public function getRelatedDatasets($id, $version){
		return $this->dsql->dsql()
			->table('related_dataset')
			->where(\npdc\lib\Db::selectVersion('dataset', $id, $version))
			->get();
	}
	/**
	 * Get science keywords
	 *
	 * @param integer $id dataset id
	 * @param integer $version dataset version
	 * @return array List of keywords
	 */
	public function getKeywords($id, $version){
		$q = $this->dsql->dsql()
			->table('dataset_keyword')
			->join('vocab_science_keyword.vocab_science_keyword_id', 'vocab_science_keyword_id', 'inner')
			->where(\npdc\lib\Db::selectVersion('dataset', $id, $version));
		return $q->order($q->expr('category, coalesce(topic, \'0\'), coalesce(term, \'0\'), coalesce(var_lvl_1, \'0\'), coalesce(var_lvl_2, \'0\'), coalesce(var_lvl_3, \'0\'), coalesce(dataset_keyword.detailed_variable, \'0\')'))
			->get();
	}
	
	/**
	 * Get free keywords
	 *
	 * @param integer $id dataset id
	 * @param integer $version dataset version
	 * @return array List of keywords
	 */
	public function getAncillaryKeywords($id, $version){
		return $this->dsql->dsql()
			->table('dataset_ancillary_keyword')
			->where(\npdc\lib\Db::selectVersion('dataset', $id, $version))
			->order('keyword')
			->get();
	}
	
	/**
	 * Get ISO topics
	 *
	 * @param integer $id dataset id
	 * @param integer $version dataset version
	 * @return array List of ISO topics
	 */
	public function getTopics($id, $version){
		return $this->dsql->dsql()
			->table('dataset_topic')
			->join('vocab_iso_topic_category.vocab_iso_topic_category_id', 'vocab_iso_topic_category_id', 'inner')
			->where(\npdc\lib\Db::selectVersion('dataset', $id, $version))
			->order('topic')
			->get();
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
		$q =$this->dsql->dsql()
			->table('dataset_link')
			->join('vocab_url_type.vocab_url_type_id', 'vocab_url_type_id', 'inner')
			->where(\npdc\lib\Db::selectVersion('dataset', $id, $version));
		if($getData){
			$q->where('dataset_link.vocab_url_type_id', 4);
		} else {
			$q->where('dataset_link.vocab_url_type_id', '<>', 4);
		}
		return $q->get();
	}
	
	/**
	 * Get urls of links
	 *
	 * @param integer $id link id
	 * @param integer $version dataset version
	 * @return array list of urls
	 */
	public function getLinkUrls($id, $version){
		return $this->dsql->dsql()
			->table('dataset_link_url')
			->where('dataset_link_id', $id)
			->where(\npdc\lib\Db::selectVersion('dataset', $version))
			->get();
	}
	
	/**
	 * Get files related to dataset
	 *
	 * @param integer $id dataset id
	 * @param integer $version dataset version
	 * @return array list of files
	 */
	public function getFiles($id, $version){
		return $this->dsql->dsql()
			->table('dataset_file')
			->join('file.file_id', 'file_id', 'inner')
			->where(\npdc\lib\Db::selectVersion('dataset', $id, $version))
			->get();
	}
	
	/**
	 * Check if person is allowed to edit record
	 *
	 * @param integer $dataset_id id of dataset
	 * @param integer $person_id id of person
	 * @return boolean user is allowed to edit
	 */
	public function isEditor($dataset_id, $person_id){
		return is_numeric($dataset_id) && is_numeric($person_id)
			? (
				count($this->dsql->dsql()
				->table('dataset_person')
				->where('dataset_id', $dataset_id)
				->where('person_id', $person_id)
				->where('dataset_version_max IS NULL')
				->where('editor')
				->get()) > 0
			) || (
				count($this->dsql
				->table('dataset')
				->where('dataset_id', $dataset_id)
				->where('creator', $person_id)
				->where('record_status IN (\'draft\', \'published\')')
				->get()) > 0
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
		return $this->dsql->dsql()
			->table('dataset')
			->where('dataset_id', $dataset_id)
			->field('dataset_version, record_status, uuid')
			->order('dataset_version DESC')
			->get();
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
		$string = '%'.$string.'%';
		$q = $this->dsql->dsql()
			->table('dataset')
			->field('*');
		$q->field($q->expr('dataset_id, date_start || \' - \' || date_end'), 'date')
			->field($q->expr('\'Dataset\''), 'content_type')
			->order('date DESC');
		if(!empty($string)){
			$operator = (\npdc\config::$db['type']==='pgsql' ? '~*' : 'LIKE');
			$s = $q->orExpr()
				->where('title', $operator, $string)
				->where('dif_id', $operator, str_replace(' ', '_', $string));
			if($summary){
				$s->where('summary', $operator, $string);
			}
			$q->where($s);
		}
		if(is_array($exclude) && count($exclude) > 0){
			$q->where('dataset_id','NOT', $exclude);
		}
		if($includeDraft) {
			$q->where('dataset_version', $q->dsql()->table('dataset', 'a')->field('max(dataset_version)')->where('a.dataset_id=dataset.dataset_id'));
		} else {
			$q->where('record_status', 'published');	
		}
		return $q->get();
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
		$q = $this->dsql
			->table('record_status_change')
			->join('person.person_id', 'person_id', 'inner')
			->where('dataset_id', $dataset_id)
			->where('version', $version)
			->order('datetime DESC');
		if($state !== null){
			$q->where('new_state', $state);
		}
		return $q->get()[0];
	}
	
	/**
	 * Get list of status changes of the dataset version
	 *
	 * @param integer $dataset_id dataset id
	 * @param integer $version dataset version
	 * @return array list of status changes with persons details
	 */
	public function getStatusChanges($dataset_id, $version){
		return $this->dsql->dsql()
			->table('record_status_change')
			->join('person.person_id', 'person_id', 'inner')
			->where('dataset_id', $dataset_id)
			->where('version', $version)
			->order('datetime DESC')
			->get();
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
		foreach($this->getCitations($dataset_id, $data['dataset_version'], 'this') as $citation){
			$url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].BASE_URL.'/dataset/'.$data['uuid'];
			$meta .= $citation['creator']
				. ' ('.substr($citation['release_date'],0,4).').'
				. ' /'.($citation['title'] ?? $data['title']).'./'
				. (!is_null($citation['version']) ? ' ('.$citation['version'].')' : '')
				. (!is_null($citation['release_place']) ? ' '.$citation['release_place'].'.' : '')
				. (!is_null($citation['editor']) ? ' Edited by '.$citation['editor'].'.' : '')
				. (!is_null($citation['publisher']) ? ' Published by '.$citation['publisher'].'.' : '')
				. ' '.$url
				. "\r\n";
		}
		$meta .= "\r\n*Use Constraints*\r\n".$data['use_constraints']."\r\n\r\n";
		$meta .= "*Quality*\r\n".$data['quality']."\r\n\r\n";

		$meta .= "*Full metadata*\r\n".getProtocol().$_SERVER['HTTP_HOST'].BASE_URL.'/dataset/'.$data['uuid'];
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
		$fields = ['dif_id','title','summary','purpose','region','date_start','date_end','quality','access_constraints','use_constraints','dataset_progress', 'originating_center', 'dif_revision_history', 'version_description', 'product_level_id', 'collection_data_type', 'extended_metadata', 'record_status', 'creator', 'duplicate_of'];
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
	
	/**
	 * Insert data set
	 *
	 * @param array $data The record to insert
	 * @return integer id of new data set
	 */
	public function insertGeneral($data){
		$values = $this->parseData($data, 'insert');
		$id = \npdc\lib\Db::insert('dataset', $values, true);
		$uuid = \Lootils\Uuid\Uuid::createV5(
			\npdc\config::$UUIDNamespace ?? \Lootils\Uuid\Uuid::createV4(),
			'dataset/'.$id.'/'.$values['dataset_version']
		)->getUUID();
		\npdc\lib\Db::update('dataset', ['dataset_id'=>$id, 'dataset_version'=>$values['dataset_version']], ['uuid'=>$uuid]);
		return $id;
	}
	
	/**
	 * Update a data set
	 *
	 * @param array $data the new data
	 * @param integer $id data set id
	 * @param integer $version data set version
	 * @return void
	 */
	public function updateGeneral($data, $id, $version){
		return \npdc\lib\Db::update('dataset', ['dataset_id'=>$id, 'dataset_version'=>$version], $this->parseData($data, 'update'));
	}
	
	private function _updateSub($tbl, $record, $data, $version){
		$oldRecord = \npdc\lib\Db::get($tbl, $record);
		$createnew = false;
		if($oldRecord['dataset_version_min'] != $version){
			foreach($data as $key=>$val){
				if($val != $oldRecord[$key]){
					$createNew = true;
				}
			}
		}
		if($createNew){
			\npdc\lib\Db::update($tbl, $record, ['dataset_version_max'=>$version-1]);
			$data['dataset_version_min'] = $version;
			return \npdc\lib\Db::insert($tbl, $data, true);
		} else {
			return \npdc\lib\Db::update($tbl, $record, $data);
		}
	}

	private function _deleteSub($tbl, $dataset_id, $version, $current, $parent = 'dataset'){
		$q = $this->dsql->dsql()
			->table($tbl)
			->where($parent.'_id', $dataset_id)
			->where('dataset_version_max', NULL);
		if(count($current) > 0){
			$q->where($tbl.'_id', 'NOT', $current);
		}
		$q->set('dataset_version_max', $version)
			->update();
		$this->dsql->dsql()
			->table($tbl)
			->where($this->dsql->expr('dataset_version_min > dataset_version_max'))
			->delete();
	}

	/**
	 * Insert spatial coverage
	 *
	 * @param array $data the data to insert
	 * @return void
	 */
	public function insertSpatialCoverage($data){
		return \npdc\lib\Db::insert('spatial_coverage', $data, true);
	}
	
	/**
	 * Update spatial coverage
	 *
	 * @param int $record record id
	 * @param array $data the data
	 * @param int $version data set versopm
	 * @return void
	 */
	public function updateSpatialCoverage($record, $data, $version){
		return $this->_updateSub('spatial_coverage', $record, $data, $version);
	}

	public function deleteSpatialCoverage($dataset_id, $version, $current){
		$this->_deleteSub('spatial_coverage', $dataset_id, $version, $current);
	}

	public function insertResolution($data){
		return \npdc\lib\Db::insert('data_resolution', $data, true);
	}
	
	public function updateResolution($record, $data, $version){
		return $this->_updateSub('data_resolution', $record, $data, $version);
	}

	public function deleteResolution($dataset_id, $version, $current){
		$this->_deleteSub('data_resolution', $dataset_id, $version, $current);
	}
	
	public function insertLocation($data){
		return \npdc\lib\Db::insert('location', $data, true);
	}

	public function updateLocation($record, $data, $version){
		return $this->_updateSub('location', $record, $data, $version);
	}

	public function deleteLocation($dataset_id, $version, $current){
		$this->_deleteSub('location', $dataset_id, $version, $current);
	}
	
	public function insertTemporalCoverage($data){
		return \npdc\lib\Db::insert('temporal_coverage', $data, true);
	}
	
	public function deleteTemporalCoverage($dataset_id, $version, $current){
		$this->_deleteSub('temporal_coverage', $dataset_id, $version, $current);
	}

	
	public function insertTemporalCoveragePeriod($data){
		return \npdc\lib\Db::insert('temporal_coverage_period', $data, true);
	}
	
	public function updateTemporalCoveragePeriod($record, $data, $version){
		return $this->_updateSub('temporal_coverage_period', $record, $data, $version, 'parent');
	}
	
	public function deleteTemporalCoveragePeriod($temporal_coverage_id, $version, $current){
		$this->_deleteSub('temporal_coverage_period', $dataset_id, $version, $current, 'temporal_coverage');
	}
	
	public function insertTemporalCoverageCycle($data){
		return \npdc\lib\Db::insert('temporal_coverage_cycle', $data, true);
	}
	
	public function updateTemporalCoverageCycle($record, $data, $version){
		return $this->_updateSub('temporal_coverage_cycle', $record, $data, $version);
	}
	
	public function deleteTemporalCoverageCycle($temporal_coverage_id, $version, $current){
		$this->_deleteSub('temporal_coverage_cycle', $dataset_id, $version, $current, 'temporal_coverage');
	}
	
	public function insertTemporalCoverageAncillary($data){
		return \npdc\lib\Db::insert('temporal_coverage_ancillary', $data, true);
	}
	
	public function updateTemporalCoverageAncillary($record, $data, $version){
		return $this->_updateSub('temporal_coverage_ancillary', $record, $data, $version);
	}
	
	public function deleteTemporalCoverageAncillary($temporal_coverage_id, $version, $current){
		$this->_deleteSub('temporal_coverage_ancillary', $dataset_id, $version, $current, 'temporal_coverage');
	}
	
	public function insertTemporalCoveragePaleo($data){
		return \npdc\lib\Db::insert('temporal_coverage_paleo', $data, true);
	}
	
	public function updateTemporalCoveragePaleo($record, $data, $version){
		return $this->_updateSub('temporal_coverage_paleo', $record, $data, $version);
	}
	
	public function deleteTemporalCoveragePaleo($temporal_coverage_id, $version, $current){
		$this->_deleteSub('temporal_coverage_paleo', $dataset_id, $version, $current, 'temporal_coverage');
	}
	
	public function insertTemporalCoveragePaleoChronounit($data){
		return \npdc\lib\Db::insert('temporal_coverage_paleo_chronounit', $data, true);
	}

	public function deleteTemporalCoveragePaleoChronounit($id, $coverage, $version){
		$q = $this->dsql->dsql()
			->table('temporal_coverage_paleo_chronounit')
			->where('temporal_coverage_paleo_id', $coverage)
			->where('dataset_version_max', null)
			->where('vocab_chronounit_id', $id)
			->set('dataset_version_max', $version)
			->update();
	}

	public function insertTopic($topic_id, $dataset_id, $dataset_version){
		return \npdc\lib\Db::insert('dataset_topic', ['dataset_id'=>$dataset_id, 'dataset_version_min'=>$dataset_version, 'vocab_iso_topic_category_id'=>$topic_id], true);
	}

	public function deleteTopic($topic_id, $dataset_id, $dataset_version){
		$this->dsql->dsql()
			->table('dataset_topic')
			->where('vocab_iso_topic_category_id', $topic_id)
			->where('dataset_id', $dataset_id)
			->where('dataset_version_max IS NULL')
			->set('dataset_version_max', $dataset_version)
			->update();
	}
	
	public function insertScienceKeyword($data){
		return \npdc\lib\Db::insert('dataset_keyword', $data, true);
	}
	
	public function updateScienceKeyword($record, $data, $version){
		return $this->_updateSub('dataset_keyword', $record, $data, $version);
	}
	
	public function deleteScienceKeyword($dataset_id, $version, $current){
		$this->_deleteSub('dataset_keyword', $dataset_id, $version, $current);
	}
	
	public function insertAncillaryKeyword($word, $id, $version){
		return \npdc\lib\Db::insert('dataset_ancillary_keyword', ['dataset_id'=>$id,'dataset_version_min'=>$version,'keyword'=>$word], true);
	}
	
	public function deleteAncillaryKeyword($word, $id, $version){
		$this->dsql
			->table('dataset_ancillary_keyword')
			->where('dataset_id', $id)
			->where('keyword', $word)
			->where('dataset_version_max', null)
			->set('dataset_version_max', $version)
			->update();
	}
	
	public function insertPerson($data){
		return \npdc\lib\Db::insert('dataset_person', $data, true);
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

	public function insertRelatedDataset($data){
		return $this->fpdo
			->insertInto('related_dataset', $data)
			->execute();
	}
	
	public function updateRelatedDataset($record, $data, $version){
		$oldRecord = $this->fpdo
			->from('related_dataset', $record)
			->fetch();
		
		$createNew = false;
		foreach($data as $key=>$val){
			if($val != $oldRecord[$key]){
				$createNew = true;
			}
		}
		if($oldRecord['dataset_version_min'] === $version && $createNew){
			$return = $this->fpdo
				->update('related_dataset', $data, $record)
				->execute() === 1;
		} elseif($createNew){
			$this->fpdo
				->update('related_dataset', ['dataset_version_max'=>$version-1], $record)
				->execute();
			$return = $this->insertRelatedDataset(array_merge($data, ['dataset_version_min'=>$version, 'dataset_id'=>$oldRecord['dataset_id']]));
		} else {
			$return = true;
		}
		return $return;
	}
	
	public function deleteRelatedDataset($dataset_id, $version, $currentRelatedDatasets){
		$q = $this->fpdo
			->update('related_dataset')
			->set('dataset_version_max', $version)
			->where('dataset_id', $dataset_id)
			->where('dataset_version_max', null);
		if(count($currentRelatedDatasets) > 0){
			foreach($currentRelatedDatasets as $related_dataset){
				if(!is_numeric($related_dataset)){
					die('Something went wrong! (e_deleteRelatedDataset '.$related_dataset.')');
				}
			}
			if(count($currentRelatedDatasets) === 1){
				$q->where('related_dataset_id <> ?',$currentRelatedDatasets[0]);
			} else {
				$q->where('related_dataset_id NOT IN ('.implode(',',$currentRelatedDatasets).')');
			}
		}
		$q->execute();
		$this->fpdo
			->deleteFrom('related_dataset')
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
