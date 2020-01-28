<?php
/**
 * Dataset controller
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\controller;

class Dataset extends Base{
	public $formId = 'datasetlist';
	public $name = 'dataset';
	public $userLevelAdd = NPDC_EDITOR;//minimum user level required to add a new dataset
	
	//list of pages in edit form
	public $pages = [
			'general'=>'General',
			'people'=>'Involved people',
			'methods'=>'Methods',
			'coverage'=>'Coverage &amp; Resolution',
			'usage'=>'Usage &amp; Citation',
			'references'=>'References &amp; links',
			'files'=>'Files',
		];
	
	/**
	 * Constructor
	 *
	 * @param object $session login information
	 */
	public function __construct($session){
		$this->session = $session;
		$this->model = new \npdc\model\Dataset();
		parent::__construct();
		if(\npdc\lib\Args::get('action') === 'files'){
			$this->listFiles();
		}
		if(\npdc\lib\Args::get('action') === 'doduplicate' && $this->access){
			$this->duplicateDataset();
		}
	}
	
	/**
	 * Check if dataset draft is different from published version
	 *
	 * @param integer $id dataset id
	 * @param integer $version new version number
	 * @return boolean
	 */
	public function recordChanged($id, $version){
		$changed = $this->generalHasChanged($id, $version);
		$tables = ['dataset_citation', 'dataset_keyword', 'dataset_link', 'dataset_person', 'dataset_project', 'dataset_publication', 'dataset_topic', 'dataset_file', 'dataset_data_center'];
		foreach($tables as $table){
			$changed = $this->tblHasChanged($table, $id, $version) || $changed;
		}
		return $changed;
	}

	/**
	 * Changing fields in forms with values from database or based on user rights
	 *
	 * @return void
	 */
	protected function alterFields() {
		$vocab = new \npdc\lib\Vocab();
		switch($this->screen){
			case 'general':
				$this->formController->form->fields->iso_topic->options = $vocab->getList('vocab_iso_topic_category');
				if($this->session->userLevel >= NPDC_ADMIN){
					$this->formController->form->fields->dif_id->disabled = false;
				} elseif($this->id !== 'new'){
					$this->formController->form->fields->dif_id->edit = false;
				}
				break;
			case 'people':
				$this->formController->form->fields->originating_center->options = $this->getOrganizations();
				$this->formController->form->fields->data_center->fields->data_center->options = $this->getOrganizations();
				$this->formController->form->fields->data_center->fields->people->options = $this->getpersons();
				$this->formController->form->fields->people->fields->organization_id->options = $this->getOrganizations();
				break;
			case 'coverage':
				$this->formController->form->fields
					->temporal_coverage->fields
					->paleo->fields
					->chronostratigraphic_unit->options = $vocab->getList('vocab_chronounit');
				$this->formController->form->fields
					->resolution->fields
					->vocab_res_hor_id->options = $vocab->getList('vocab_res_hor');
				$this->formController->form->fields
					->resolution->fields
					->vocab_res_vert_id->options = $vocab->getList('vocab_res_vert');
				$this->formController->form->fields
					->resolution->fields
					->vocab_res_time_id->options = $vocab->getList('vocab_res_time');
				break;
			case 'references':
				$this->formController->form->fields
					->links->fields
					->type->options = $vocab->getList('vocab_url_type');
				$options = [];
				foreach($this->model->getList() as $ds){
					$options[$ds['dataset_id']] = $ds['title'];
				}
				$this->formController->form->fields->related_dataset->fields->dataset->fields->dataset_id->options = $options;
				break;
		}
	}

	/**
	 * Populate the form fields with record information
	 *
	 * @param array $baseData
	 * @return void
	 */
	protected function loadForm($baseData){
		$_SESSION[$this->formId]['data'] = $baseData;
		$vocab = new \npdc\lib\Vocab();
		$vocabModel = new \npdc\model\Vocab();
		switch ($this->screen){
			case 'general':
				if(\npdc\lib\Args::get('action') === 'new'){
					unset($_SESSION[$this->formId]);
				} else {
					$iso_topics = $this->model->getTopics($this->id, $this->version);
					$_SESSION[$this->formId]['data']['iso_topic'] = [];
					foreach($iso_topics as $topic){
						$_SESSION[$this->formId]['data']['iso_topic'][] = $topic['vocab_iso_topic_category_id'];
					}
					$_SESSION[$this->formId]['data']['science_keywords'] = [];
					foreach($this->model->getKeywords($this->id, $this->version) as $keyword){
						$_SESSION[$this->formId]['data']['science_keywords'][] = ['id'=>$keyword['dataset_keyword_id']
							, 'keyword_id'=>$keyword['vocab_science_keyword_id']
							, 'keyword'=>$vocab->formatTerm('vocab_science_keyword', $keyword, false, true)
							, 'detailed_variable'=>$keyword['free_text']];
					}
					$keywords = $this->model->getAncillaryKeywords($this->id, $this->version);
					$words = [];
					foreach($keywords as $keyword){
						$words[] = $keyword['keyword'];
					}
					$_SESSION[$this->formId]['data']['keywords'] = $words;
				}
				break;
			case 'people':
				$_SESSION[$this->formId]['data']['people'] = $this->model->getPersons($this->id, $this->version);
				
				foreach($this->model->getDataCenter($this->id, $this->version) as $rowid=>$row){
					$basekey = 'data_center_'.$rowid;
					$_SESSION[$this->formId]['data'][$basekey.'_id'] = $row['dataset_data_center_id'];
					$_SESSION[$this->formId]['data'][$basekey.'_data_center'] = $row['organization_id'];
					$people = $this->model->getDataCenterPerson($row['dataset_data_center_id'], $this->version);
					$dc_people = [];
					foreach($people as $person){
						$dc_people[] = $person['person_id'];
					}
					$_SESSION[$this->formId]['data'][$basekey.'_people'] = $dc_people;	
				}

				break;
			case 'methods':
				foreach($this->model->getPlatform($this->id, $this->version) as $rowid=>$row){
					$basekey = 'platform_'.$rowid;
					$_SESSION[$this->formId]['data'][$basekey.'_id'] = $row['platform_id'];
					$_SESSION[$this->formId]['data'][$basekey.'_platform_id'] = $row['vocab_platform_id'];
					$_SESSION[$this->formId]['data'][$basekey.'_platform'] = $vocab->formatTerm('vocab_platform', $vocabModel->getTermById('vocab_platform', $row['vocab_platform_id']), true,true);
					foreach($this->model->getInstrument($row['platform_id'], $this->version) as $subrowid=>$subrow){
						$basesubkey = $basekey.'_instrument_'.$subrowid;
						$_SESSION[$this->formId]['data'][$basesubkey.'_id'] = $subrow['instrument_id'];
						$_SESSION[$this->formId]['data'][$basesubkey.'_instrument_id'] = $subrow['vocab_instrument_id'];
						$_SESSION[$this->formId]['data'][$basesubkey.'_instrument'] = $vocab->formatTerm('vocab_instrument', $vocabModel->getTermById('vocab_instrument', $subrow['vocab_instrument_id']),true,true);
						$_SESSION[$this->formId]['data'][$basesubkey.'_technique'] = $subrow['technique'];
						$_SESSION[$this->formId]['data'][$basesubkey.'_number_of_sensors'] = $subrow['number_of_sensors'];
						foreach($this->model->getSensor($subrow['instrument_id'], $this->version) as $subsubrowid=>$subsubrow){
							$basesubsubkey = $basesubkey.'_sensor_'.$subsubrowid;
							$_SESSION[$this->formId]['data'][$basesubsubkey.'_id'] = $subsubrow['sensor_id'];
							$_SESSION[$this->formId]['data'][$basesubsubkey.'_sensor_id'] = $subsubrow['vocab_instrument_id'];
							$_SESSION[$this->formId]['data'][$basesubsubkey.'_sensor'] = $vocab->formatTerm('vocab_instrument', $vocabModel->getTermById('vocab_instrument', $subsubrow['vocab_instrument_id']),true,true);
							$_SESSION[$this->formId]['data'][$basesubsubkey.'_technique'] = $subsubrow['technique'];
						}
					
					}
				}
				break;
			case 'coverage':
				$_SESSION[$this->formId]['data']['period'] = [$baseData['date_start'], $baseData['date_end']];
				$fields_spatial = ['wkt', 'depth_min', 'depth_max', 'depth_unit', 'altitude_min', 'altitude_max', 'altitude_unit', 'type', 'label', 'spatial_coverage_id'];
				foreach($this->model->getLocations($this->id, $this->version) as $row){
					$_SESSION[$this->formId]['data']['location'][] = [
							  'id'=>$row['location_id']
							, 'location_id'=>$row['vocab_location_id']
							, 'location'=>$vocab->formatTerm('vocab_location', $vocabModel->getTermById('vocab_location', $row['vocab_location_id']),true,true)
							, 'detailed'=>$row['detailed']];
				}
				foreach($this->model->getSpatialCoverages($this->id, $this->version) as $rowid=>$row){
					foreach($fields_spatial as $field){
						switch($field){
							case 'type':
								$_SESSION[$this->formId]['data']['spatial_coverage_type_'.$rowid] = 'spatial_coverage_'.$row['type'].'_'.$rowid;
								break;
							case 'spatial_coverage_id':
								$_SESSION[$this->formId]['data']['spatial_coverage_id_'.$rowid] = $row[$field];
								break;
							default:
								$key = (substr($field, -5) === '_unit' 
									? 'unit_spatial_coverage_'.substr($field, 0, -5).'_min' 
									: 'spatial_coverage_'.$field);
								$_SESSION[$this->formId]['data'][$key.'_'.$rowid] = $row[$field];
						}
					}
				}
				
				foreach($this->model->getTemporalCoverages($this->id, $this->version) as $rowid=>$row){
					$_SESSION[$this->formId]['data']['temporal_coverage_'.$rowid.'_id'] = $row['temporal_coverage_id'];
					foreach($this->model->getTemporalCoveragesGroup('period', $row['temporal_coverage_id'], $this->version) as $subrowid=>$subrow){
						$_SESSION[$this->formId]['data']['temporal_coverage_'.$rowid.'_dates_'.$subrowid.'_id'] = $subrow['temporal_coverage_period_id'];
						$_SESSION[$this->formId]['data']['temporal_coverage_'.$rowid.'_dates_'.$subrowid.'_range'] = [$subrow['date_start'], $subrow['date_end']];
					}
					
					foreach($this->model->getTemporalCoveragesGroup('cycle', $row['temporal_coverage_id'], $this->version) as $subrowid=>$subrow){
						$_SESSION[$this->formId]['data']['temporal_coverage_'.$rowid.'_periodic_'.$subrowid.'_id'] = $subrow['temporal_coverage_cycle_id'];
						$_SESSION[$this->formId]['data']['temporal_coverage_'.$rowid.'_periodic_'.$subrowid.'_name'] = $subrow['name'];
						$_SESSION[$this->formId]['data']['temporal_coverage_'.$rowid.'_periodic_'.$subrowid.'_dates'] = [$subrow['date_start'], $subrow['date_end']];
						$_SESSION[$this->formId]['data']['temporal_coverage_'.$rowid.'_periodic_'.$subrowid.'_periodic_cycle'] = $subrow['sampling_frequency'];
						$_SESSION[$this->formId]['data']['unit_temporal_coverage_'.$rowid.'_periodic_'.$subrowid.'_periodic_cycle'] = $subrow['sampling_frequency_unit'];
					}
					
					foreach($this->model->getTemporalCoveragesGroup('paleo', $row['temporal_coverage_id'], $this->version) as $subrowid=>$subrow){
						$_SESSION[$this->formId]['data']['temporal_coverage_'.$rowid.'_paleo_'.$subrowid.'_id'] = $subrow['temporal_coverage_paleo_id'];
						$_SESSION[$this->formId]['data']['temporal_coverage_'.$rowid.'_paleo_'.$subrowid.'_start'] = $subrow['start_value'];
						$_SESSION[$this->formId]['data']['unit_temporal_coverage_'.$rowid.'_paleo_'.$subrowid.'_start'] = $subrow['start_unit'];
						$_SESSION[$this->formId]['data']['temporal_coverage_'.$rowid.'_paleo_'.$subrowid.'_end'] = $subrow['end_value'];
						$_SESSION[$this->formId]['data']['unit_temporal_coverage_'.$rowid.'_paleo_'.$subrowid.'_end'] = $subrow['end_unit'];
						$_SESSION[$this->formId]['data']['temporal_coverage_'.$rowid.'_paleo_'.$subrowid.'_chronostratigraphic_unit'] = [];
						foreach($this->model->getTemporalCoveragePaleoChronounit($subrow['temporal_coverage_paleo_id'], $this->version) as $subsubrow){
							$_SESSION[$this->formId]['data']['temporal_coverage_'.$rowid.'_paleo_'.$subrowid.'_chronostratigraphic_unit'][] = $subsubrow['vocab_chronounit_id'];
						}
					}
					
					foreach($this->model->getTemporalCoveragesGroup('ancillary', $row['temporal_coverage_id'], $this->version) as $subrowid=>$subrow){
						$_SESSION[$this->formId]['data']['temporal_coverage_'.$rowid.'_ancillary_'.$subrowid.'_id'] = $subrow['temporal_coverage_ancillary_id'];
						$_SESSION[$this->formId]['data']['temporal_coverage_'.$rowid.'_ancillary_'.$subrowid.'_keyword'] = $subrow['keyword'];
					}
				}
				
				$fields_resolution = ['latitude_resolution', 'longitude_resolution', 'vocab_res_hor_id', 'vertical_resolution', 'vocab_res_vert_id', 'temporal_resolution', 'vocab_res_time_id', 'data_resolution_id'];
				foreach($this->model->getResolution($this->id, $this->version) as $rowid=>$row){
					foreach($fields_resolution as $field){
						switch($field){
							case 'data_resolution_id':
								$_SESSION[$this->formId]['data']['resolution_'.$rowid.'_id'] = $row[$field];
								break;
							default:
								$_SESSION[$this->formId]['data']['resolution_'.$rowid.'_'.$field] = $row[$field];
						}
					}
				}
				break;
			case 'usage':
				$fields = ['creator', 'editor', 'title', 'series_name', 'release_date', 'release_place', 'publisher', 'version', 'issue_identification', 'presentation_form', 'other', 'persistent_identifier_type', 'persistent_identifier_identifier', 'online_resource', 'type'];
				$citation = $this->model->getCitations($this->id, $this->version, 'this')[0];
				if(is_null($citation)){
					$_SESSION[$this->formId]['data']['citation_this_creator'] = $this->model->getAuthors($this->id, $this->version);
				} else {
					$_SESSION[$this->formId]['data']['citation_this_id'] = $citation['dataset_citation_id'];
					foreach($fields as $field){
						$_SESSION[$this->formId]['data']['citation_this_'.$field] = $field === 'release_date' ? [$citation[$field]] : $citation[$field];
					}	
				}
				break;
			case 'references':
				$fields = ['id', 'type', 'title', 'description', 'mime', 'protocol'];
				foreach($this->model->getLinks($this->id, $this->version) as $linkid=>$link){
					foreach($fields as $field){
						$key = 'links_'.$linkid.'_'.$field;
						switch($field){
							case 'type':
								$field = 'vocab_url_type_id';
								break;
							case 'mime':
								$field = 'mime_type_id';
								break;
							case 'id':
								$field = 'dataset_link_id';
								break;
						}
						$_SESSION[$this->formId]['data'][$key] = $link[$field];
					}
					foreach($this->model->getLinkUrls($link['dataset_link_id'], $this->version) as $urlid=>$linkUrl){
						$key = 'links_'.$linkid.'_url_'.$urlid.'_';
						$_SESSION[$this->formId]['data'][$key.'id'] = $linkUrl['dataset_link_url_id'];
						$_SESSION[$this->formId]['data'][$key.'url'] = $linkUrl['url'];
					}
				}
				$_SESSION[$this->formId]['data']['publications'] = $this->model->getPublications($this->id, $this->version, false);
				$_SESSION[$this->formId]['data']['projects'] = $this->model->getProjects($this->id, $this->version, false);
				foreach($this->model->getCitations($this->id, $this->version, 'other') as $rowid=>$row){
					$_SESSION[$this->formId]['data']['citation_'.$rowid.'_id'] = $row['dataset_citation_id'];
					foreach($fields as $field){
						$_SESSION[$this->formId]['data']['citation_'.$rowid.'_'.$field] = $field === 'release_date' ? [$row[$field]] : $row[$field];
					}
				}
				foreach($this->model->getRelatedDatasets($this->id, $this->version) as $setId=>$set){
					$key = 'related_dataset_'.$key.'_';
					$_SESSION[$this->formId]['data'][$key.'id'] = $set['related_dataset_id'];
					$_SESSION[$this->formId]['data'][$key.'same'] = $set['same'] ? 'true' : 'false';
					$_SESSION[$this->formId]['data'][$key.'relation'] = $set['relation'];
					$_SESSION[$this->formId]['data'][$key.'dataset_url'] = $set['url'];
					$_SESSION[$this->formId]['data'][$key.'dataset_doi'] = $set['doi'];
					$_SESSION[$this->formId]['data'][$key.'dataset_dataset_id'] = $set['internal_related_dataset_id'];
				}
				break;
			case 'files':
				$fields = ['id', 'type', 'title', 'description', 'mime', 'protocol'];
				foreach($this->model->getLinks($this->id, $this->version, true) as $linkid=>$link){
					foreach($fields as $field){
						$key = 'links_'.$linkid.'_'.$field;
						switch($field){
							case 'type':
								$field = 'vocab_url_type_id';
								break;
							case 'mime':
								$field = 'mime_type_id';
								break;
							case 'id':
								$field = 'dataset_link_id';
								break;
						}
						$_SESSION[$this->formId]['data'][$key] = $link[$field];
					}
					foreach($this->model->getLinkUrls($link['dataset_link_id'], $this->version) as $urlid=>$linkUrl){
						$key = 'links_'.$linkid.'_url_'.$urlid.'_';
						$_SESSION[$this->formId]['data'][$key.'id'] = $linkUrl['dataset_link_url_id'];
						$_SESSION[$this->formId]['data'][$key.'url'] = $linkUrl['url'];
					}
				}
				$fileModel = new \npdc\model\File();
				$fields = ['id', 'file', 'name', 'title', 'description', 'perms'];
				foreach($this->model->getFiles($this->id, $this->version) as $nr=>$file){
					foreach($fields as $field){
						$key = 'file_'.$field.'_'.$nr;
						switch($field){
							case 'id':
								$field = 'file_id';
								break;
							case 'file':
								$field = 'name';
								break;
							case 'perms':
								$field = 'default_access';
								break;
						}
						$_SESSION[$this->formId]['data'][$key] = $file[$field];
					}
				}
				foreach($fileModel->getDrafts('dataset:'.$this->id) as $nr=>$file){
					foreach($fields as $field){
						$key = 'file_'.$field.'_n_'.$nr;
						switch($field){
							case 'id':
								$field = 'file_id';
								break;
							case 'file':
								$field = 'name';
								break;
							case 'perms':
								$field = 'default_access';
								break;
						}
						$_SESSION[$this->formId]['data'][$key] = $file[$field];
					}
				}
				
				break;
		}
	}
	
	/**
	 * insert record or save updated version of record
	 *
	 * @return void
	 */
	protected function doSave(){
		if(\npdc\lib\Args::get('action') === 'new'){
			$_SESSION[$this->formId]['data']['dataset_version'] = 1;
			$_SESSION[$this->formId]['data']['record_status'] = 'draft';
			$_SESSION[$this->formId]['data']['creator'] = $this->session->userId;
			$this->id = $this->model->insertGeneral($_SESSION[$this->formId]['data']);
			$saved = $this->id !== false;
			$this->saveKeywords();
			$this->saveTopics();

			//save current user as metadata author and editor
			$_SESSION[$this->formId]['data']['people_person_id_new_1'] = $this->session->userId;
			$_SESSION[$this->formId]['data']['people_name_new_1'] = $this->session->name;
			$_SESSION[$this->formId]['data']['people_organization_id_new_1'] = $this->session->organization_id;
			$_SESSION[$this->formId]['data']['people_role_new_1'] = ['Metadata Author'];
			$_SESSION[$this->formId]['data']['people_editor_new_1'] = true;
			$this->savePeople();
		} else {
			switch($this->screen){
				case 'general': 
					if($_SESSION[$this->formId]['db_action'] !== 'insert'){
						$saved = $this->model->updateGeneral($_SESSION[$this->formId]['data'], $this->id, $this->version) !== false;
					}
					$this->saveKeywords();
					$this->saveTopics();
					break;
				case 'people':
					$saved = $this->model->updateGeneral([
						'originating_center'=>$_SESSION[$this->formId]['data']['originating_center'], 
						'data_center'=>$_SESSION[$this->formId]['data']['data_center']
						]
						, $this->id, $this->version) !== false;
					$this->savePeople();
					$this->saveDataCenter();
					break;
				case 'coverage':
					$this->saveLocations();
					$this->saveSpatialCoverage();
					$this->saveTemporalCoverage();
					$this->saveResolution();
					$saved = true;
					break;
				case 'usage':
					$saved = $this->model->updateGeneral(
						[
							'dataset_progress'=>$_SESSION[$this->formId]['data']['dataset_progress'],
							'quality'=>$_SESSION[$this->formId]['data']['quality'],
							'access_constraints'=>$_SESSION[$this->formId]['data']['access_constraints'],
							'use_constraints'=>$_SESSION[$this->formId]['data']['use_constraints']
						
						], $this->id, $this->version) !== false;
					$this->saveCitation('this');
					break;
				case 'methods':
					$this->savePlatform();
					$saved = true;
					break;
				case 'references':
					$this->saveLink();
					$this->saveProjects();
					$this->savePublications();
					$this->saveCitation('other');
					$this->saveRelatedDatasets();
					$saved = true;
					break;
				case 'files':
					$this->saveFiles();
					$this->saveLink(true);
					$saved = true;
			}
		}
		$_SESSION['notice'] = $saved 
			? '<p>Your changes have been saved.</p>' 
			: 'Something went wrong when trying to save your record';
		if($saved){
			$url = BASE_URL.'/dataset/'.$this->id.'/';
			unset($_SESSION[$this->formId]);
			if($_POST['gotoNext'] == 1){
				$url .= 'edit/'.current(array_keys(array_slice($this->pages, array_search($this->screen, array_keys($this->pages)) + 1, 1)));
			} else {
				$url .= $this->version;
			}
			header('Location: '.$url);
			echo 'redirect';
			die();
		}
	}

	/**
	 * DUPLICATERS
	 */

	/**
	 * Create duplicate of a dataset
	 *
	 * @return void
	 */
	private function duplicateDataset(){
		$this->id = \npdc\lib\Args::get('id');
		$this->version = \npdc\lib\Args::get('version');
		$data = $this->model->getById($this->id, $this->version);

		$data['created_from'] = $data['uuid'];
		foreach(['dataset_id','published','insert_timestamp','uuid'] as $key){
			unset($data[$key]);
		}
		foreach(['dataset_version'=>1,'creator'=>$this->session->userId,'dif_id'=>'[COPY]'.$data['dif_id'],'title'=>'[COPY]'.$data['title'],'record_status'=>'draft'] as $key=>$val){
			$data[$key] = $val;
		}
		$this->newId = $this->model->insertGeneral($data);
		$this->duplicateKeywords();
		$this->duplicateTopics();
		$this->duplicatePeople();
		$this->duplicateDataCenter();
		$this->duplicateLocations();
		$this->duplicateSpatialCoverage();
		$this->duplicateTemporalCoverage();
		$this->duplicateResolution();
		$this->duplicateCitation();
		$this->duplicateLink();
		$this->duplicatePlatform();
		$this->duplicateProjects();
		$this->duplicatePublications();
		$this->duplicateRelatedDatasets();
		$_SESSION['notice'] = 'The dataset has been duplicated. Please review the details and alter where needed.<br/><br/>Please be aware:<ul><li>Files (or links to it) have not been transferred (the whole idea of duplicates is that you can easily make a similar description for different files)</li><li>Edits done in the original after this moment will <strong>not</strong> be transfered to this duplicate, nor the other way</li></ul>';
		$data = $this->model->getById($this->newId, 1);
		header('Location: '.BASE_URL.'/dataset/'.$data['uuid']);
		die();
	}
	
	/**
	 * Copy keywords from original dataset to duplicate
	 *
	 * @return void
	 */
	private function duplicateKeywords(){
		foreach($this->model->getKeywords($this->id, $this->version) as $keyword){
			$data = [
				'dataset_id'=>$this->newId, 
				'dataset_version_min'=>1,
				'vocab_science_keyword_id'=>$keyword['vocab_science_keyword_id'],
				'free_text' =>$keyword['free_text']
			];
			$this->model->insertScienceKeyword($data);
		}
		foreach($keywords = $this->model->getAncillaryKeywords($this->id, $this->version) as $word){
			$this->model->insertAncillaryKeyword($word['keyword'], $this->newId, 1);
		}
	}

	/**
	 * Copy iso topics from original dataset to duplicate
	 *
	 * @return void
	 */
	private function duplicateTopics(){
		foreach($this->model->getTopics($this->id, $this->version) as $topic){
			$this->model->insertTopic($topic['vocab_iso_topic_category_id'], $this->newId, 1);
		}
	}

	/**
	 * Copy people from original dataset to duplicate
	 *
	 * @return void
	 */
	private function duplicatePeople(){
		foreach($this->model->getPersons($this->id, $this->version) as $person){
			$data = ['dataset_id'=>$this->newId, 'dataset_version_min'=>1, 'person_id'=>$person['person_id'], 'organization_id'=>$person['organization_id'], 'editor'=>$person['editor'], 'role'=>$person['role'], 'sort'=>$person['sort']];
			$this->model->insertPerson($data);
		}
	}

	/**
	 * Copy data center from original dataset to duplicate
	 *
	 * @return void
	 */
	private function duplicateDataCenter(){
		foreach($this->model->getDataCenter($this->id, $this->version) as $dataCenter){
			$data_center_id = $this->model->insertDataCenter(['dataset_id'=>$this->newId, 'dataset_version_min'=>1, 'organization_id'=>$dataCenter['organization_id']]);
			foreach($this->model->getDataCenterPerson($dataCenter['dataset_data_center_id'], $this->version) as $person){
				$this->model->insertDataCenterPerson(['dataset_data_center_id'=>$data_center_id, 'dataset_version_min'=>1, 'person_id'=>$person['person_id']]);
			}
		}
	}

	/**
	 * Copy locations from original dataset to duplicate
	 *
	 * @return void
	 */
	private function duplicateLocations(){
		foreach($this->model->getLocations($this->id, $this->version) as $location){
			$this->model->insertLocation([
				'dataset_id'=>$this->newId, 
				'dataset_version_min'=>1,
				'vocab_location_id'=>$location['vocab_location_id'],
				'detailed'=>$location['detailed']
			]);
		}
	}

	/**
	 * Copy spatial coverages from original dataset to duplicate
	 * 
	 * @return void
	 */
	private function duplicateSpatialCoverage(){
		foreach($this->model->getSpatialCoverages($this->id, $this->version) as $sc){
			$data = ['dataset_id'=>$this->newId, 'dataset_version_min'=>1];
			foreach(['wkt', 'depth_min', 'depth_max', 'depth_unit', 'altitude_min', 'altitude_max', 'altitude_unit', 'type', 'label'] as $key){
				$data[$key] = $sc[$key];
			}
			$this->model->insertSpatialCoverage($data);
		}
	}

	/**
	 * Copy temporal coverages from original dataset to duplicate
	 *
	 * @return void
	 */
	private function duplicateTemporalCoverage(){
		foreach($this->model->getTemporalCoverages($this->id, $this->version) as $tc){
			$temporalCoverageId = $this->model->insertTemporalCoverage(['dataset_id'=>$this->newId, 'dataset_version_min'=>1]);
			foreach(['period', 'cycle', 'paleo', 'ancillary'] as $group){
				foreach($this->model->getTemporalCoveragesGroup($group, $tc['temporal_coverage_id'], $this->version) as $tcg){
					switch($group){
						case 'period':
						$this->model->insertTemporalCoveragePeriod([
								'date_start'=>$tcg['date_start'],
								'date_end'=>$tcg['date_end'],
								'temporal_coverage_id'=>$temporalCoverageId,
								'dataset_version_min'=>1
							]);
							break;
						case 'cycle':
							$this->model->insertTemporalCoverageCycle([
								'name'=>$tcg['name'],
								'date_start'=>$tcg['date_start'],
								'date_end'=>$tcg['date_end'],
								'sampling_frequency'=>$tcg['sampling_frequency'],
								'sampling_frequency_unit'=>$tcg['sampling_frequency_unit'],
								'temporal_coverage_id'=>$temporalCoverageId,
								'dataset_version_min'=>1
							]);
							break;
						case 'paleo':
							$gid = $this->model->insertTemporalCoveragePaleo([
								'start_value'=>$tcg['start_value'],
								'start_unit'=>$tcg['start_unit'],
								'end_value'=>$tcg['end_value'],
								'end_unit'=>$tcg['end_unit'],
								'temporal_coverage_id'=>$temporalCoverageId,
								'dataset_version_min'=>1
							]);
							foreach($this->model->getTemporalCoveragePaleoChronounit($tcg['temporal_coverage_paleo_id'], $this->version) as $tcgg){
								$this->model->insertTemporalCoveragePaleoChronounit([
									'temporal_coverage_paleo_id'=>$gid,
									'dataset_version_min'=>1,
									'vocab_chronounit_id'=>$tcgg['vocab_chronounit_id']
								]);
							}
							break;
						case 'ancillary':
							$this->model->insertTemporalCoverageAncillary([
								'keyword'=>$_SESSION[$this->formId]['data'][$baseId.$serial.'_keyword'],
								'temporal_coverage_id'=>$temporalCoverageId,
								'dataset_version_min'=>1
							]);
							break;
					}
				}
			}
		}
	}

	/**
	 * Copy resolution from original dataset to duplicate
	 *
	 * @return void
	 */
	private function duplicateResolution(){
		$fields = ['latitude_resolution', 'longitude_resolution', 'vocab_res_hor_id', 'vertical_resolution', 'vocab_res_vert_id', 'temporal_resolution', 'vocab_res_time_id', 'data_resolution_id'];
		foreach($this->model->getResolution($this->id, $this->version) as $resolution){
			$data = ['dataset_id'=>$this->newId, 'dataset_version_min'=>1];
			foreach($fields as $field){
				$data[$field] = $resolution[$field];
			}
			$this->model->insertResolution($data);
		}
	}

	/**
	 * Copy citation from original dataset to duplicate
	 *
	 * @return void
	 */
	private function duplicateCitation(){
		$fields = ['creator', 'editor', 'title', 'series_name', 'release_date', 'release_place', 'publisher', 'version', 'issue_identification', 'presentation_form', 'other', 'persistent_identifier_type', 'persistent_identifier_identifier', 'online_resource', 'type'];
		foreach($this->model->getCitations($this->id, $this->version) as $citation){
			$data = ['dataset_id'=>$this->newId, 'dataset_version_min'=>1];
			foreach($fields as $field){
				$data[$field] = $citation[$field];
			}
			$this->model->insertCitation($data);
		}
	}

	/**
	 * Copy links from original dataset to duplicate
	 * Get Data links are omitted
	 *
	 * @return void
	 */
	private function duplicateLink(){
		foreach($this->model->getLinks($this->id, $this->version) as $link){
			$link_id = $this->model->insertLink([
				'dataset_id'=>$this->newId,
				'dataset_version_min'=>1,
				'vocab_url_type_id'=>$link['vocab_url_type_id'],
				'title'=>$link['title'],
				'description'=>$link['description']
			]);
			foreach($this->model->getLinkUrls($link['dataset_link_id'], $this->version) as $linkUrl){
				$this->model->insertLinkUrl([
					'dataset_link_id'=>$link_id,
					'dataset_version_min'=> 1,
					'url'=>$linkUrl['url']
				]);
			}
		}
	}

	/**
	 * Copy platform (including instruments and sensors) from original dataset to duplicate
	 *
	 * @return void
	 */
	private function duplicatePlatform(){
		foreach($this->model->getPlatform($this->id, $this->version) as $platform){
			$platform_id = $this->model->insertPlatform([
				'dataset_id'=>$this->newId, 
				'dataset_version_min'=>1, 
				'vocab_platform_id'=>$platform['vocab_platform_id']
			]);
			foreach($this->model->getInstrument($platform['platform_id'], $this->version) as $instrument){
				$instrument_id = $this->model->insertInstrument([
					'vocab_instrument_id'=>$instrument['vocab_instrument_id'],
					'technique'=>$instrument['technique'],
					'number_of_sensors'=>$instrument['number_of_sensors'],
					'platform_id'=>$platform_id,
					'dataset_version_min'=>1
				]);
				foreach($this->model->getSensor($instrument['instrument_id'], $this->version) as $sensor){
					$this->model->insertSensor([
						'vocab_instrument_id'=>$instrument['vocab_instrument_id'],
						'technique'=>$instrument['technique'],
						'instrument_id'=>$instrument_id,
						'dataset_version_min'=>1
					]);
				}
			}
		}
	}

	/**
	 * Link duplicated dataset to same projects as original dataset was linked to
	 *
	 * @return void
	 */
	private function duplicateProjects(){
		foreach($this->model->getProjects($this->id, $this->version, false) as $project){
			$this->model->insertProject([
				'dataset_id'=>$this->newId, 
				'project_id'=>$project['project_id'],
				'project_version_min'=>$project['project_version_min'],
				'dataset_version_min'=>1
			]);
		}
	}

	/**
	 * Link duplicated dataset to same publications as original dataset was linked to
	 *
	 * @return void
	 */
	private function duplicatePublications(){
		foreach($this->model->getPublications($this->id, $this->version, false) as $publication){
			$this->model->insertPublication([
				'dataset_id'=>$this->newId, 
				'publication_id'=>$publication['publication_id'],
				'publication_version_min'=>$publication['publication_version_min'],
				'dataset_version_min'=>1
			]);
		}
	}

	/**
	 * Link duplicated dataset to samen datasets as original dataset was linked to
	 * Additionally links are added between the original dataset and the duplicate
	 *
	 * @return void
	 */
	private function duplicateRelatedDatasets(){
		foreach($this->model->getRelatedDatasets($this->id, $this->version) as $set){
			$this->model->insertRelatedDataset([
				'same'=>$set['same'],
				'relation'=>$set['relation'],
				'url'=>$set['url'],
				'doi'=>$set['doi'],
				'internal_related_dataset_id'=>$set['internal_related_dataset_id'],
				'dataset_id'=>$this->newId,
				'dataset_version_min'=>1
			]);
		}
		
		$this->model->insertRelatedDataset([
			'same'=>0,
			'relation'=>'Similar dataset',
			'dataset_id'=>$this->id,
			'dataset_version_min'=>$this->version,
			'internal_related_dataset_id'=>$this->newId
		]);
		$this->model->insertRelatedDataset([
			'same'=>0,
			'relation'=>'Similar dataset',
			'dataset_id'=>$this->newId,
			'dataset_version_min'=>1,
			'internal_related_dataset_id'=>$this->id
		]);
	}

	/**
	 * Save keywords
	 *
	 * @return void
	 */
	private function saveKeywords(){
		$keyword = [];
		$loopId = 'science_keywords_keyword_id_';
		$sort = 1;
		foreach(array_keys($_SESSION[$this->formId]['data']) as $key){
			if(substr($key, 0, strlen($loopId)) === $loopId){
				$data = [
					'dataset_id'=>$this->id, 
					'vocab_science_keyword_id'=>$_SESSION[$this->formId]['data'][$key],
					'free_text' => $_SESSION[$this->formId]['data']['science_keywords_detailed_variable_'.substr($key, strlen($loopId))]
				];
				if(strpos($key, '_new_') !== false){
					$data = array_merge($data, ['dataset_version_min'=>$this->version]);
					$keyword[] = $this->model->insertScienceKeyword($data);
				} elseif(strpos($key, '_new') === false) {
					$keyword_id = $_SESSION[$this->formId]['data']['science_keywords_id_'.substr($key, strlen($loopId))];
					$return = $this->model->updateScienceKeyword($keyword_id, $data, $this->version);
					$keyword[] = is_bool($return) ? $keyword_id : $return;
				}
				$sort++;
			}
		}
		$v = $this->version-1;
		$this->model->deleteScienceKeyword($this->id, $v, $keyword);
		
		//Ancillary keywords
		$currentKeywords = $this->model->getAncillaryKeywords($this->id, $this->version);
		$words = [];
		foreach($currentKeywords as $row){
			$words[] = $row['keyword'];
		}
		$new = array_diff($_SESSION[$this->formId]['data']['keywords'], $words);
		$old = array_diff($words, $_SESSION[$this->formId]['data']['keywords']);
		if(count($old) > 0){
			foreach($old as $word){
				$this->model->deleteAncillaryKeyword($word, $this->id, $this->version-1);
			}
		}
		if(count($new) > 0){
			foreach($new as $word){
				$this->model->insertAncillaryKeyword($word, $this->id, $this->version);
			}
		}
	}

	/**
	 * SAVE PARTS
	 */
	
	/**
	 * Save iso topics
	 *
	 * @return void
	 */
	private function saveTopics(){
		$currentTopics = $this->model->getTopics($this->id, $this->version);
		$topics = [];
		foreach($currentTopics as $row){
			$topics[] = $row['vocab_iso_topic_category_id'];
		}
		$new = array_diff($_SESSION[$this->formId]['data']['iso_topic'], $topics);
		$old = array_diff($topics, $_SESSION[$this->formId]['data']['iso_topic']);
		if(count($old) > 0){
			foreach($old as $topic){
				$this->model->deleteTopic($topic, $this->id, $this->version-1);
			}
		}
		if(count($new) > 0){
			foreach($new as $topic){
				$this->model->insertTopic($topic, $this->id, $this->version);
			}
		}
	}
	
	/**
	 * Save (bi-directional) link to projects
	 *
	 * @return void
	 */
	private function saveProjects(){
		$projects = [];
		$loopId = 'projects_project_id_';
		$projectModel = new \npdc\model\Project();
		
		foreach(array_keys($_SESSION[$this->formId]['data']) as $key){
			if(substr($key, 0, strlen($loopId)) === $loopId){
				$projects[] = $_SESSION[$this->formId]['data'][$key];
				if(strpos($key, '_new_') !== false){
					$data = [
						'dataset_id'=>$this->id, 
						'project_id'=>$_SESSION[$this->formId]['data'][$key],
						'project_version_min'=>$projectModel->getVersions($_SESSION[$this->formId]['data'][$key])[0]['project_version'],
						'dataset_version_min'=>$this->version
					];
					$this->model->insertProject($data);
				}
			}
		}
		$v = $this->version-1;
		$this->model->deleteProject($this->id, $v, $projects);
	}

	/**
	 * Save (bi-directional) link to publications
	 *
	 * @return void
	 */
	private function savePublications(){
		$publications = [];
		$loopId = 'publications_publication_id_';
		$publicationModel = new \npdc\model\Publication();
		
		foreach(array_keys($_SESSION[$this->formId]['data']) as $key){
			if(substr($key, 0, strlen($loopId)) === $loopId){
				$publications[] = $_SESSION[$this->formId]['data'][$key];
				if(strpos($key, '_new_') !== false){
					$data = [
						'dataset_id'=>$this->id, 
						'publication_id'=>$_SESSION[$this->formId]['data'][$key],
						'publication_version_min'=>$publicationModel->getVersions($_SESSION[$this->formId]['data'][$key])[0]['publication_version'],
						'dataset_version_min'=>$this->version
					];
					$this->model->insertPublication($data);
				}
			}
		}
		$v = $this->version-1;

		$this->model->deletePublication($this->id, $v, $publications);
	}

	/**
	 * Save relate datasets
	 *
	 * @return void
	 */
	private function saveRelatedDatasets(){
		$related = [];
		$loopId = 'related_dataset_';
		$serials = [];
		foreach(array_keys($_SESSION[$this->formId]['data']) as $key){
			if(substr($key, 0, strlen($loopId)) === $loopId){
				$serials[] = substr($key
						, strlen($loopId)
						, strpos($key, '_', strlen($loopId))-strlen($loopId)
					);
			}
		}
		foreach(array_unique($serials) as $serial){
			$data = [
				'same'=>$_SESSION[$this->formId]['data'][$loopId.$serial.'_same'] == 'true' ? 1 : 0,
				'relation'=>$_SESSION[$this->formId]['data'][$loopId.$serial.'_relation'],
				'url'=>$_SESSION[$this->formId]['data'][$loopId.$serial.'_dataset_url'],
				'doi'=>$_SESSION[$this->formId]['data'][$loopId.$serial.'_dataset_doi'],
				'internal_related_dataset_id'=>$_SESSION[$this->formId]['data'][$loopId.$serial.'_dataset_dataset_id']
			];
			if(empty($_SESSION[$this->formId]['data']['related_dataset_'.$serial.'_id'])){
				$data['dataset_id'] = $this->id;
				$data['dataset_version_min'] = $this->version;
				$related[] = $this->model->insertRelatedDataset($data);
			} else {
				$record_id = $_SESSION[$this->formId]['data']['related_dataset_'.$serial.'_id'];
				$return = $this->model->updateRelatedDataset($record_id, $data, $this->version);
				$related[] = !is_bool($return) ? $return : $record_id;	
			}
		}
		$v = $this->version-1;
		$this->model->deleteRelatedDataset($this->id, $v, $related, $type);
	}
	
	/**
	 * Save locations
	 *
	 * @return void
	 */
	private function saveLocations() {
		$locations = [];
		$loopId = 'location_location_id_';
		foreach(array_keys($_SESSION[$this->formId]['data']) as $key){
			if(substr($key, 0, strlen($loopId)) === $loopId){
				$data = [
					'dataset_id'=>$this->id, 
					'vocab_location_id'=>$_SESSION[$this->formId]['data'][$key],
					'detailed' => $_SESSION[$this->formId]['data']['location_detailed_'.substr($key, strlen($loopId))]
				];
				if(strpos($key, '_new_') !== false){
					$data = array_merge($data, ['dataset_version_min'=>$this->version]);
					$locations[] = $this->model->insertLocation($data);
				} elseif(strpos($key, '_new') === false) {
					$location_id = $_SESSION[$this->formId]['data']['location_id_'.substr($key, strlen($loopId))];
					$return = $this->model->updateLocation($location_id, $data, $this->version);
					$locations[] = is_bool($return) ? $location_id : $return;
				}
			}
		}
		$v = $this->version-1;
		$this->model->deleteLocation($this->id, $v, $locations);
	}
	
	/**
	 * Save spatial coverage
	 *
	 * @return void
	 */
	private function saveSpatialCoverage(){
		$coverages = [];
		$loopId = 'spatial_coverage_wkt_';
		foreach(array_keys($_SESSION[$this->formId]['data']) as $key){
			if(substr($key, 0, strlen($loopId)) === $loopId && substr($key, -4) !== '_new'){
				$fields = ['wkt', 'depth_min', 'depth_max', 'depth_unit', 'altitude_min', 'altitude_max', 'altitude_unit', 'type', 'label'];
				$data = ['dataset_id'=> $this->id];
				$nr = substr($key, strlen($loopId));
				foreach($fields as $field){
					$key = (substr($field, -5) === '_unit' 
						? 'unit_spatial_coverage_'.substr($field, 0, -5).'_min' 
						: 'spatial_coverage_'.$field)
						. '_'.$nr;
					$data[$field] = $field === 'type' 
						? explode('_', $_SESSION[$this->formId]['data'][$key])[2] 
						: $_SESSION[$this->formId]['data'][$key];
				}
				if(strpos($key, '_new') !== false){
					$data['dataset_version_min'] = $this->version;
					$coverages[] = $this->model->insertSpatialCoverage($data);
				} else {
					$record_id = $_SESSION[$this->formId]['data']['spatial_coverage_id_'.$nr];
					$return = $this->model->updateSpatialCoverage($record_id, $data, $this->version);
					$coverages[] = !is_bool($return) ? $return : $record_id;	
				}
			}
		}
		$v = $this->version-1;
		$this->model->deleteSpatialCoverage($this->id, $v, $coverages);
	}
	
	/**
	 * Save temporal coverage
	 *
	 * @return void
	 */
	private function saveTemporalCoverage(){
		$loopId = 'temporal_coverage_';
		$serials = [];
		$current = [];
		foreach (array_keys($_SESSION[$this->formId]['data']) as $key){
			if(substr($key, 0, strlen($loopId)) === $loopId){
				$serials[] = substr($key
						, strlen($loopId)
						, strpos($key, '_', strlen($loopId))-strlen($loopId)
					);
			}
		}
		foreach(array_unique($serials) as $serial){
			//first make sure the temporal_coverage is stored in the database
			if(strpos($serial, 'new') !== false){
				$temporalCoverageId = $this->model->insertTemporalCoverage(['dataset_id'=>$this->id, 'dataset_version_min'=>$this->version]);
			} else {
				$temporalCoverageId = $_SESSION[$this->formId]['data']['temporal_coverage_'.$serial.'_id'];
			}
			$current[] = $temporalCoverageId;
			$loopId2 = $loopId.$serial.'_';
			foreach(['dates', 'periodic', 'paleo', 'ancillary'] as $group){
				$loopId3 = $loopId2.$group.'_';
				$serials2 = [];
				$current2 = [];
				foreach (array_keys($_SESSION[$this->formId]['data']) as $key){
					if(substr($key, 0, strlen($loopId3)) === $loopId3){
						$serials2[] = substr($key
								, strlen($loopId3)
								, strpos($key, '_', strlen($loopId3))-strlen($loopId3)
							);
					}
				}
				foreach(array_unique($serials2) as $serial2){
					$current2[] = $this->saveTemporalCoverageGroup($group, $temporalCoverageId, $loopId3, $serial2);
				}
				$v = $this->version-1;
				
				switch ($group){
					case 'dates':
						$this->model->deleteTemporalCoveragePeriod($temporalCoverageId, $v, $current2);
						$this->model->updateGeneral(['date_start'=>$this->date_start,'date_end'=>$this->date_end], $this->id, $this->version);
						break;
					case 'periodic':
						$this->model->deleteTemporalCoverageCycle($temporalCoverageId, $v, $current2);
						break;
					case 'paleo':
						$this->model->deleteTemporalCoveragePaleo($temporalCoverageId, $v, $current2);
						break;
					case 'ancillary':
						$this->model->deleteTemporalCoverageAncillary($temporalCoverageId, $v, $current2);
						break;
				}
			}
		}
		$v = $this->version-1;
		$this->model->deleteTemporalCoverage($this->id, $v, $current);
	}
	
	/**
	 * Save subgroup of a temporal coverage
	 *
	 * @param string $group type of group
	 * @param integer $temporalCoverageId id of the parent temporal coverage
	 * @param string $baseId prefix with which to get the data from the session
	 * @param string $serial sequence number of the group
	 * @return void
	 */
	private function saveTemporalCoverageGroup($group, $temporalCoverageId, $baseId, $serial){
		switch ($group){
			case 'dates':
				$data = [
					'date_start'=>$_SESSION[$this->formId]['data'][$baseId.$serial.'_range'][0],
					'date_end'=>$_SESSION[$this->formId]['data'][$baseId.$serial.'_range'][1],
					'temporal_coverage_id'=>$temporalCoverageId
				];
				$this->date_start = (!isset($this->date_start) || $this->date_start > $data['date_start']) ? $data['date_start'] : $this->date_start;
				$this->date_end = (!isset($this->date_end) || $this->date_end < $data['date_end']) ? $data['date_end'] : $this->date_end;
				if(strpos($serial, 'new') !== false){
					$data['dataset_version_min'] = $this->version;
					return $this->model->insertTemporalCoveragePeriod($data);
				} else {
					return $this->model->updateTemporalCoveragePeriod($_SESSION[$this->formId]['data'][$baseId.$serial.'_id'], $data, $this->version);
				}
				break;
			case 'periodic':
				$data = ['name'=>$_SESSION[$this->formId]['data'][$baseId.$serial.'_name'],
					'date_start'=>$_SESSION[$this->formId]['data'][$baseId.$serial.'_dates'][0],
					'date_end'=>$_SESSION[$this->formId]['data'][$baseId.$serial.'_dates'][1],
					'sampling_frequency'=>$_SESSION[$this->formId]['data'][$baseId.$serial.'_periodic_cycle'],
					'sampling_frequency_unit'=>$_SESSION[$this->formId]['data']['unit_'.$baseId.$serial.'_periodic_cycle'],
					'temporal_coverage_id'=>$temporalCoverageId
				];
				if(strpos($serial, 'new') !== false){
					$data['dataset_version_min'] = $this->version;
					return $this->model->insertTemporalCoverageCycle($data);
				} else {
					return $this->model->updateTemporalCoverageCycle($_SESSION[$this->formId]['data'][$baseId.$serial.'_id'], $data, $this->version);
				}
				break;
			case 'paleo':
				$data = ['start_value'=>$_SESSION[$this->formId]['data'][$baseId.$serial.'_start'],
					'start_unit'=>$_SESSION[$this->formId]['data']['unit_'.$baseId.$serial.'_start'],
					'end_value'=>$_SESSION[$this->formId]['data'][$baseId.$serial.'_end'],
					'end_unit'=>$_SESSION[$this->formId]['data']['unit_'.$baseId.$serial.'_end'],
					'temporal_coverage_id'=>$temporalCoverageId
				];
				if(strpos($serial, 'new') !== false){
					$data['dataset_version_min'] = $this->version;
					$id = $this->model->insertTemporalCoveragePaleo($data);
				} else {
					$id = $this->model->updateTemporalCoveragePaleo($_SESSION[$this->formId]['data'][$baseId.$serial.'_id'], $data, $this->version);
				}
				$currentUnits = $this->model->getTemporalCoveragePaleoChronounit($id, $this->version);
				$units = [];
				foreach($currentUnits as $row){
					$units[] = $row['vocab_chronounit_id'];
				}
				$new = array_diff($_SESSION[$this->formId]['data'][$baseId.$serial.'_chronostratigraphic_unit'], $units);
				$old = array_diff($units, $_SESSION[$this->formId]['data'][$baseId.$serial.'_chronostratigraphic_unit']);
				if(count($old) > 0){
					foreach($old as $unit){
						$this->model->deleteTemporalCoveragePaleoChronounit($unit, $id, $this->version-1);
					}
				}
				if(count($new) > 0){
					foreach($new as $unit){
						$this->model->insertTemporalCoveragePaleoChronounit([
							'temporal_coverage_paleo_id'=>$id,
							'dataset_version_min'=>$this->version,
							'vocab_chronounit_id'=>$unit
						]);
					}
				}
				return $id;
				break;
			case 'ancillary':
				$data = [
					'keyword'=>$_SESSION[$this->formId]['data'][$baseId.$serial.'_keyword'],
					'temporal_coverage_id'=>$temporalCoverageId
				];
				if(strpos($serial, 'new') !== false){
					$data['dataset_version_min'] = $this->version;
					return $this->model->insertTemporalCoverageAncillary($data);
				} else {
					return $this->model->updateTemporalCoverageAncillary($_SESSION[$this->formId]['data'][$baseId.$serial.'_id'], $data, $this->version);
				}
				break;
		}
	}
	
	/**
	 * Save the data resolution
	 *
	 * @return void
	 */
	private function saveResolution(){
		$resolutions = [];
		$loopId = 'resolution_';
		$serials = [];
		foreach(array_keys($_SESSION[$this->formId]['data']) as $key){
			if(substr($key, 0, strlen($loopId)) === $loopId){
				$serials[] = substr($key
						, strlen($loopId)
						, strpos($key, '_', strlen($loopId))-strlen($loopId)
					);
			}
		}
		foreach(array_unique($serials) as $serial){
			$fields = ['latitude_resolution', 'longitude_resolution', 'vocab_res_hor_id', 'vertical_resolution', 'vocab_res_vert_id', 'temporal_resolution', 'vocab_res_time_id'];
			$data = ['dataset_id'=>$this->id];
			foreach($fields as $field){
				$key = 'resolution_'.$serial.'_'.$field;
				$data[$field] = $_SESSION[$this->formId]['data'][$key];
			}

			if(strpos($serial, 'new') !== false){
				$data['dataset_version_min'] = $this->version;
				$resolutions[] = $this->model->insertResolution($data);
			} else {
				$nr = substr($key, strlen($loopId));
				$record_id = $_SESSION[$this->formId]['data']['resolution_id'.$nr];

				$return = $this->model->updateResolution($record_id, $data, $this->version);
				$resolutions[] = !is_bool($return) ? $return : $record_id;	
			}
		}
		$v = $this->version-1;
		$this->model->deleteResolution($this->id, $v, $resolutions);
	}

	/**
	 * Link people to dataset
	 *
	 * @return void
	 */
	private function savePeople(){
		$persons = [];
		$loopId = 'people_person_id_';
		$sort = 1;
		foreach(array_keys($_SESSION[$this->formId]['data']) as $key){
			if(substr($key, 0, strlen($loopId)) === $loopId){
				$persons[] = $_SESSION[$this->formId]['data'][$key];
				$record = [
					'dataset_id'=>$this->id, 
					'person_id'=>$_SESSION[$this->formId]['data'][$key]
				];
				$data = [];
				$data['editor'] = !empty($_SESSION[$this->formId]['data']['people_editor_'.substr($key, strlen($loopId))]) ? 1 : 0;
				$data['organization_id'] = $_SESSION[$this->formId]['data']['people_organization_id_'.substr($key, strlen($loopId))];
				$data['role'] = '["'.implode('","', $_SESSION[$this->formId]['data']['people_role_'.substr($key, strlen($loopId))]).'"]';
				$data['sort'] = $sort;
				if(strpos($key, '_new_') === false){
					$saved = $this->model->updatePerson($record, $data, $this->version) === false ? false : $saved;
				} else {
					$data = array_merge($data, $record, ['dataset_version_min'=>$this->version]);
					$this->model->insertPerson($data);
				}
				$sort++;
			}
		}
		$v = $this->version-1;
		return $this->model->deletePerson($this->id, $v, $persons) !== false;	
	}
	
	/**
	 * Save data center
	 *
	 * @return void
	 */
	private function saveDataCenter(){
		$current = [];
		$loopId = 'data_center_';
		$serials = [];
		foreach(array_keys($_SESSION[$this->formId]['data']) as $key){
			if(substr($key, 0, strlen($loopId)) === $loopId){
				$serials[] = substr($key
						, strlen($loopId)
						, strpos($key, '_', strlen($loopId))-strlen($loopId)
					);
			}
		}
		foreach(array_unique($serials) as $serial){
			$data = [
				'dataset_id'=>$this->id,
				'organization_id'=>$_SESSION[$this->formId]['data'][$loopId.$serial.'_data_center']
			];
			if(strpos($serial, 'new') !== false){
				$data['dataset_version_min'] = $this->version;
				$data_center_id = $this->model->insertDataCenter($data);
				foreach($_SESSION[$this->formId]['data'][$loopId.$serial.'_people'] as $person){
					$this->model->insertDataCenterPerson(['dataset_data_center_id'=>$data_center_id, 'dataset_version_min'=>$this->version, 'person_id'=>$person]);
				}
			} else {
				$data_center_id = $_SESSION[$this->formId]['data'][$loopId.$serial.'_id'];
				$return = $this->model->updateDataCenter($data_center_id, $data, $this->version);
				if(is_numeric($return)) {
					$data_center_id = $return;
				}
				$currentPersons = $this->model->getDataCenterPerson($data_center_id, $this->version);
				$persons = [];
				foreach($currentPersons as $row){
					$persons[] = $row['person_id'];
				}
				$new = array_diff($_SESSION[$this->formId]['data'][$loopId.$serial.'_people'], $persons);
				$old = array_diff($persons, $_SESSION[$this->formId]['data'][$loopId.$serial.'_people'] ?? []);
				
				if(count($old) > 0){
					foreach($old as $person){
						$this->model->deleteDataCenterPerson($person, $data_center_id, $this->version-1);
					}
				}
				if(count($new) > 0){
					foreach($new as $person){
						$this->model->insertDataCenterPerson([
							'person_id'=>$person, 
							'dataset_data_center_id'=>$data_center_id, 
							'dataset_version_min'=>$this->version
						]);
					}
				}
			}
			$current[] = $data_center_id;
		}
		$this->model->deleteDataCenter($this->id, $this->version-1, $current);
	}
	
	/**
	 * Save citation
	 *
	 * @param string $type either this or other
	 * @return void
	 */
	private function saveCitation($type = null){
		$citations = [];
		$loopId = 'citation_';
		$serials = [];
		foreach(array_keys($_SESSION[$this->formId]['data']) as $key){
			if(substr($key, 0, strlen($loopId)) === $loopId){
				$serials[] = substr($key
						, strlen($loopId)
						, strpos($key, '_', strlen($loopId))-strlen($loopId)
					);
			}
		}
		foreach(array_unique($serials) as $serial){
			$fields = ['creator', 'editor', 'title', 'series_name', 'release_date', 'release_place', 'publisher', 'version', 'issue_identification', 'presentation_form', 'other', 'persistent_identifier_type', 'persistent_identifier_identifier', 'online_resource', 'type'];
			$data = [];
			foreach($fields as $field){
				$key = 'citation_'.$serial.'_'.$field;
				if($field === 'release_date' && is_array($_SESSION[$this->formId]['data'][$key])){
					$data[$field] = $_SESSION[$this->formId]['data'][$key][0];
				} else {
					$data[$field] = $_SESSION[$this->formId]['data'][$key];
				}
			}
			if(empty($_SESSION[$this->formId]['data']['citation_'.$serial.'_id'])){
				$data['dataset_id'] = $this->id;
				$data['dataset_version_min'] = $this->version;
				$citations[] = $this->model->insertCitation($data);
			} else {
				$record_id = $_SESSION[$this->formId]['data']['citation_'.$serial.'_id'];
				$return = $this->model->updateCitation($record_id, $data, $this->version);
				$citations[] = !is_bool($return) ? $return : $record_id;	
			}
		}
		$v = $this->version-1;
		$this->model->deleteCitation($this->id, $v, $citations, $type);
	}
	
	/**
	 * Save the platform
	 *
	 * @return void
	 */
	private function savePlatform(){
		$current = [];
		$loopId = 'platform_';
		$serials = [];
		foreach(array_keys($_SESSION[$this->formId]['data']) as $key){
			if(substr($key, 0, strlen($loopId)) === $loopId){
				$serials[] = substr($key
						, strlen($loopId)
						, strpos($key, '_', strlen($loopId))-strlen($loopId)
					);
			}
		}
		foreach(array_unique($serials) as $serial){
			$data = [
				'dataset_id'=>$this->id,
				'vocab_platform_id'=>$_SESSION[$this->formId]['data'][$loopId.$serial.'_platform_id']
			];
			if(strpos($serial, 'new') !== false){
				$data['dataset_version_min'] = $this->version;
				$platform_id = $this->model->insertPlatform($data);
			} else {
				$platform_id = $_SESSION[$this->formId]['data'][$loopId.$serial.'_id'];
				$return = $this->model->updatePlatform($platform_id, $data, $this->version);
				if(!is_bool($return)) {
					$platform_id = $return;
				}
			}
			$current[] = $platform_id;
			$this->saveCharacteristics('platform', $platform_id, $loopId.$serial);
			$this->saveInstrument($loopId.$serial, $platform_id);
		}
		$this->model->deletePlatform($this->id, $this->version-1, $current);
	}
	
	/**
	 * Save the instruments
	 *
	 * @param string $base_id prefix with which data is retreived from the session
	 * @param integer $platform_id id of the platform
	 * @return void
	 */
	private function saveInstrument($base_id, $platform_id){
		$loopId = $base_id.'_instrument_';
		$serials = [];
		$current = [];
		foreach(array_keys($_SESSION[$this->formId]['data']) as $key){
			if(substr($key, 0, strlen($loopId)) === $loopId){
				$serials[] = substr($key
						, strlen($loopId)
						, strpos($key, '_', strlen($loopId))-strlen($loopId)
					);
			}
		}
		foreach(array_unique($serials) as $serial){
			$data = [
				'vocab_instrument_id'=>$_SESSION[$this->formId]['data'][$loopId.$serial.'_instrument_id'],
				'technique'=>$_SESSION[$this->formId]['data'][$loopId.$serial.'_technique'],
				'number_of_sensors'=>$_SESSION[$this->formId]['data'][$loopId.$serial.'_number_of_sensors'],
				'platform_id'=>$platform_id
				
			];
			$return = null;
			if(strpos($serial, 'new') !== false){
				$data['dataset_version_min'] = $this->version;
				$instrument_id = $this->model->insertInstrument($data);
			} else {
				$instrument_id = $_SESSION[$this->formId]['data'][$loopId.$serial.'_id'];
				$return = $this->model->updateInstrument($instrument_id, $data, $this->version);
				if(!is_bool($return)){
					$instrument_id = $return;
				}
			}
			$current[] = $instrument_id;
			$this->saveCharacteristics('instrument', $instrument_id, $loopId.$serial);
			//$this->saveSensor($loopId.$serial, $instrument_id);
		}
		$this->model->deleteInstrument($platform_id, $this->version-1, $current);
	}
	
	/**
	 * Save sensor
	 *
	 * @param string $base_id prefix with which data is retreived from the session
	 * @param integer $instrument_id instrument to which sensor belongs
	 * @return void
	 */
	private function saveSensor($base_id, $instrument_id){
		$loopId = $base_id.'_sensor_';
		$serials = [];
		$current = [];
		foreach(array_keys($_SESSION[$this->formId]['data']) as $key){
			if(substr($key, 0, strlen($loopId)) === $loopId){
				$serials[] = substr($key
						, strlen($loopId)
						, strpos($key, '_', strlen($loopId))-strlen($loopId)
					);
			}
		}
		foreach(array_unique($serials) as $serial){
			$data = [
				'vocab_instrument_id'=>$_SESSION[$this->formId]['data'][$loopId.$serial.'_sensor_id'],
				'technique'=>$_SESSION[$this->formId]['data'][$loopId.$serial.'_technique'],
				'instrument_id'=>$instrument_id
				
			];
			if(strpos($serial, 'new') !== false){
				$data['dataset_version_min'] = $this->version;
				$sensor_id = $this->model->insertSensor($data);
			} else {
				$sensor_id = $_SESSION[$this->formId]['data'][$loopId.$serial.'_id'];
				$return = $this->model->updateSensor($sensor_id, $data, $this->version);
				if(!is_bool($return)){
					$sensor_id = $return;	
				}
			}
			$current[] = $sensor_id;
			$this->saveCharacteristics('sensor', $sensor_id, $loopId.$serial);
		}
		$this->model->deleteSensor($instrument_id, $this->version-1, $current);
	}
	
	/**
	 * Save characteristics of platform, instrument or sensor
	 *
	 * @param string $type platform, instrument or sensor
	 * @param integer $record_id id of $type
	 * @param string $base_id prefix with which data is retreived from the session
	 * @return void
	 */
	private function saveCharacteristics($type, $record_id, $base_id){
		if(!in_array($type, ['platform', 'instrument', 'sensor'])){
			die('wrong type in saveCharacteristics');
		}
		$loopId = $base_id.'_characteristics_id_';
		$serials = [];
		foreach(array_keys($_SESSION[$this->formId]['data']) as $key){
			if(substr($key, 0, strlen($loopId)) === $loopId){
				$serials[] = substr($key
						, strlen($loopId));
			}
		}
		$current = [];
		foreach(array_unique($serials) as $serial){
			$data = [
				'name'=>$_SESSION[$this->formId]['data'][$base_id.'_characteristics_name_'.$serial],
				'description'=>$_SESSION[$this->formId]['data'][$base_id.'_characteristics_description_'.$serial],
				'data_type'=>$_SESSION[$this->formId]['data'][$base_id.'_characteristics_datatype_'.$serial],
				'unit'=>$_SESSION[$this->formId]['data'][$base_id.'_characteristics_unit_'.$serial],
				'value'=>$_SESSION[$this->formId]['data'][$base_id.'_characteristics_value_'.$serial],
				$type.'_id'=>$record_id
			];
			if(strpos($serial, 'new') === false){
				$return = $this->model->updateCharacteristics($_SESSION[$this->formId]['data'][$base_id.'_characteristics_id_'.$serial], $data, $this->version);
				$current[] = is_bool($return) ? $_SESSION[$this->formId]['data'][$base_id.'_characteristics_id_'.$serial] : $return;
			} else {
				$data['dataset_version_min'] = $this->version;
				$current[] = $this->model->insertCharacteristics($data);
			}
		}
		$this->model->deleteCharacteristics([$type, $record_id], $this->version-1, $current);
	}

	/**
	 * Save link to external site
	 *
	 * @param boolean $getData are we saving getData links or other links?
	 * @return void
	 */
	private function saveLink($getData = false){
		$links = [];
		foreach ($this->model->getLinks($this->id, $this->version, !$getData) as $link) {
			$links[] = $link['dataset_link_id'];
		}
		$loopId = 'links_';
		$serials = [];
		foreach(array_keys($_SESSION[$this->formId]['data']) as $key){
			if(substr($key, 0, strlen($loopId)) === $loopId){
				$serials[] = substr($key
						, strlen($loopId)
						, strpos($key, '_', strlen($loopId))-strlen($loopId)
					);
			}
		}
		foreach(array_unique($serials) as $serial){
			$fields = ['type', 'title', 'description'];
			$data = [];
			foreach($fields as $field){
				$key = $loopId.$serial.'_'.$field;
				switch($field){
					case 'type':
						$field = 'vocab_url_type_id';
						break;
					case 'mime':
						$field = 'mime_type_id';
						break;
				}
				$data[$field] = $_SESSION[$this->formId]['data'][$key];
			}

			if(strpos($serial, 'new') !== false){
				$data['dataset_id'] = $this->id;
				$data['dataset_version_min'] = $this->version;
				$record_id = $this->model->insertLink($data);
			} else {
				$record_id = $_SESSION[$this->formId]['data']['links_'.$serial.'_id'];

				$return = $this->model->updateLink($record_id, $data, $this->version);
				if(!is_bool($return)){
					$record_id = $return;
				}	
			}
			$links[] = $record_id;
			
			$loopId2 = $loopId.$serial.'_url_';
			$serials2 = [];
			$current2 = [];
			foreach (array_keys($_SESSION[$this->formId]['data']) as $key){
				if(substr($key, 0, strlen($loopId2)) === $loopId2){
					$serials2[] = substr($key
							, strlen($loopId2)
							, strpos($key, '_', strlen($loopId2))-strlen($loopId2)
						);
				}
			}
			foreach(array_unique($serials2) as $serial2){
				//save the url
				if(strpos($serial2, 'new') !== false){
					$data = [
						'dataset_link_id'=>$record_id,
						'dataset_version_min'=> $this->version,
						'url'=>$_SESSION[$this->formId]['data'][$loopId2.$serial2.'_url']
					];
					$current2[] = $this->model->insertLinkUrl($data);
				} else {
					$return = $this->model->updateLinkUrl($_SESSION[$this->formId]['data'][$loopId2.$serial2.'_id'], ['url'=>$_SESSION[$this->formId]['data'][$loopId2.$serial2.'_url'], 'dataset_link_id'=>$record_id], $this->version);
					$current2[] = !is_bool($return) ? $return : $_SESSION[$this->formId]['data'][$loopId2.$serial2.'_id'];
				}
				$this->model->deleteLinkUrl($record_id, $this->version-1, $current2);
			}
		}
		$v = $this->version-1;
		$this->model->deleteLink($this->id, $v, $links);
	}
	
	/**
	 * Save files to be attached to dataset
	 *
	 * @return void
	 */
	private function saveFiles(){
		$loopId = 'file_id_';
		$serials = [];
		foreach(array_keys($_SESSION[$this->formId]['data']) as $key){
			if(substr($key, 0, strlen($loopId)) === $loopId){
				$serials[] = substr($key
						, strlen($loopId)
					);
			}
		}
		$files = [];
		$fileModel = new \npdc\model\File();
		if(count($serials) > 0){
			foreach($serials as $serial){
				$files[] = $_SESSION[$this->formId]['data'][$loopId.$serial];
				if(substr($serial, 0, 1) === 'n'){
					$data = [
						'dataset_id'=>$this->id,
						'dataset_version_min'=> $this->version,
						'file_id'=>$_SESSION[$this->formId]['data'][$loopId.$serial]
						];
					$this->model->insertFile($data);
					$fileModel->updateFile($data['file_id'], ['record_state'=>'complete']);
				}
			}
		}
		$v = $this->version - 1;
		$this->model->deleteFile($this->id, $v, $files);
		$fileModel->cancelDrafts('dataset:'. $this->id);
	}
	
	/** 
	 * FILE ACCESS
	 */

	/**
	 * list the available files and request/get access
	 *
	 * @return void
	 */
	private function listFiles(){
		$dataset = \npdc\lib\Args::get('id');
		if(\npdc\lib\Args::exists('version') && $this->canEdit){
			$this->data = $this->model->getById($dataset, \npdc\lib\Args::get('version'));
		} else {
			$this->data = $this->model->getById($dataset);
		}
		$datasetFiles = $this->model->getFiles($dataset, $this->data['dataset_version']);
		$files = [];
		$tmpFiles = [];
		$access = ['public'];
		switch (\npdc\lib\Args::get('subaction')){
			case 'request':
				if(isset($_POST['request'])){
					if(count($_POST['files']) === 0){
						$this->error = 'Please select which files you wish to access';
					} elseif(empty($_POST['request'])){
						$this->error = 'Please provide information on why you want access';
					} else {
						$requestModel = new \npdc\model\Request();
						$requestId = $requestModel->insertRequest(['person_id'=>$this->session->userId, 'reason'=>$_POST['request'], 'dataset_id'=>$dataset]);
						foreach($_POST['files'] as $file){
							$requestModel->insertFile(['access_request_id'=>$requestId, 'file_id'=>$file]);
						}
						$_SESSION['notice'] = 'Your request has been saved with number '.$requestId;

						$mail = new \npdc\lib\Mailer();
						$personModel = new \npdc\model\Person();
						$sendable = false;
						foreach($this->model->getPersons($this->data['dataset_id'], $this->data['dataset_version']) as $person){
							if($person['editor'] == 1){
								$personData = $personModel->getById($person['person_id']);
								if($personData['mail'] !== null){
									$mail->to($personData['mail'], $personData['name']);
									$sendable = true;
								}
							}
						}
						if($sendable){
							$mail->subject('New data request');
							$text = 'Dear data provider'.",\r\n\r"
								. 'There is a new data request for data you provided at '.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].BASE_URL.'/request/'.$requestId."\r\n\r\nPlease process this request.\r\n\r\nKind regards,\r\n". \npdc\config::$siteName;
							$mail->text($text);
							$mail->send();	
						}
						
						$mail = new \npdc\lib\Mailer();
						$mail->to(\npdc\config::$mail['contact'], \npdc\config::$siteName);
						$mail->subject('New data request');
						$text = 'Dear admin'.",\r\n\r"
							. 'There is a new data request at '.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].BASE_URL.'/request/'.$requestId."\r\n\r\nPlease make sure the request is processed.\r\n\r\n"
							. ($sendable ? 'A message has been sent to the researchers to notify them of the request' : 'It was NOT possible to send a notification to the researchers')
							. "\r\n\r\nKind regards,\r\n". \npdc\config::$siteName;
						$mail->text($text);
						$mail->send();
						header('Location: '.BASE_URL.'/request/'.$requestId);
						die();
					}
				}
				return;
			case 'selected':
				$tmpFiles = $_POST['files'];
			case 'all':
				if($this->session->userLevel > NPDC_PUBLIC){
					$access[] = 'login';
				}
			case 'public':
				foreach($datasetFiles as $file){
					if(in_array($file['default_access'], $access) && (empty($tmpFiles) || in_array($file['file_id'], $tmpFiles))){
						$files[] = $file['file_id'];
					}
				}
				break;
		}
		if(!empty($files)){
			$zip = new \npdc\lib\ZipFile();
			$zip->create(preg_replace('/[^A-Za-z0-9\-_]/', '', str_replace(' ', '_', $this->session->name ?? 'guest')));
			$zip->setDataset($this->data['dataset_id']);
			if(!empty($this->session->userId)){
				$zip->setUser($this->session->userId);
			}
			if(!empty($_POST['contact'])){
				$zip->setGuestName($_POST['contact']);
			}
			$zip->addMeta($this->model->generateMeta($this->data['dataset_id']));
			foreach($files as $file){
				$zip->addFile($file);
			}
			header('Location: '.$zip->redirect);
			die();
		}
	}
}