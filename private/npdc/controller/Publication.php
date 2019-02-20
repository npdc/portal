<?php

/**
 * Publication controller
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\controller;

class Publication extends Base{
	public $formId = 'publicationlist';
	public $name = 'publication';
	public $userLevelAdd = NPDC_EDITOR;//minimum user level required to add a new dataset
	
	/**
	 * Constructor
	 *
	 * @param object $session login information
	 *
	 */
	public function __construct($session, $limited = false){
		$this->session = $session;
		$this->model = new \npdc\model\Publication();
		if(!$limited){
			parent::__construct();
		}
	}
	
	/**
	 * Check if draft is different from published version
	 *
	 * @param integer $id id of record
	 * @param integer $version version number of draft
	 * @return boolean did record change
	 */
	public function recordChanged($id, $version){
		$changed = $this->generalHasChanged($id, $version);
		$tables = ['publication_keyword', 'publication_person', 'project_publication', 'dataset_publication'];
		foreach($tables as $table){
			$changed = $this->tblHasChanged($table, $id, $version) || $changed;
		}
		return $changed;
	}

	/**
	 * Populate fields
	 *
	 * @return void
	 */
	protected function alterFields(){
		$this->formController->form->fields->people->fields->organization_id->options = $this->getOrganizations();
	}
	
	/**
	 * Populate fields with record data
	 *
	 * @param array $baseData
	 * @return void
	 */
	protected function loadForm($baseData){
		if($this->id === 'new'){
			unset($_SESSION[$this->formId]);
			if(!empty($_GET['doi'])){
				$this->loadFromDOI($_GET['doi']);
			} else {
				$_SESSION[$this->formId]['data']['people'][] = [
					'person_id'=>$this->session->userId, 
					'name'=>$this->session->name, 
					'organization_id'=>$this->session->organization_id, 
					'editor'=>true, 
					'contact'=>true
				];	
			}
		} else {
			$_SESSION[$this->formId]['data'] = $baseData;

			$keywords = $this->model->getKeywords($this->id, $this->version);
			$words = [];
			foreach($keywords as $keyword){
				$words[] = $keyword['keyword'];
			}
			$_SESSION[$this->formId]['data']['keywords'] = $words;

			$_SESSION[$this->formId]['data']['people'] = $this->model->getPersons($this->id, $this->version);
			foreach($_SESSION[$this->formId]['data']['people'] as &$row){
				if(empty($row['person_id'])){
					$row['person_id'] = 'quickadd';
				}
				unset($row);
			}
			$_SESSION[$this->formId]['data']['date'] = [$baseData['date']];
			$_SESSION[$this->formId]['data']['projects'] = $this->model->getProjects($this->id, $this->version, false);
			$_SESSION[$this->formId]['data']['datasets'] = $this->model->getDatasets($this->id, $this->version, false);
		}
	}

	/**
	 * Load metadata of publication based on DOI
	 * 
	 * First database is checked to see if we have the DOI already.
	 * - If so, give warning
	 * - If not, load data from dx.doi.org and provide form to add publication
	 *
	 * @param string $doi the DOI of the publication the user wants to add
	 * @return void
	 */
	private function loadFromDOI($doi){
		$doi = substr($doi, strpos($doi, '10.'));
		$existing = $this->model->getByDOI($doi);
		if($existing != false){
			$_SESSION['errors'] = 'This publication  with doi '.$doi.' is already in the database, you can find it <a href="'.BASE_URL.'/publication/'.$existing['publication_id'].'">here</a>. If this is a different publication, or access to this page is denied, please contact the NPDC.';
		} else {
			$curl = new \npdc\lib\CurlWrapper(['Accept: application/vnd.citationstyles.csl+json']);
			$data = json_decode($curl->get('http://dx.doi.org/'.$doi));
			if(empty($data)){
				$_SESSION['errors'] = 'No details found based in the DOI \''.$doi.'\'';
			} else {
				$_SESSION['notice'] = 'Found the details below based on your DOI ('.$doi.'), please check the details and update where needed';
				foreach(['title', 'issue','volume'] as $field){
					$_SESSION[$this->formId]['data'][$field] = $data->$field;
				}
				$_SESSION[$this->formId]['data']['pages'] = $data->page;
				$_SESSION[$this->formId]['data']['doi'] = $doi;
				$_SESSION[$this->formId]['data']['journal'] = $data->{'container-title'};
				$curl2 = new \npdc\lib\CurlWrapper();
				$_SESSION[$this->formId]['data']['url'] = $curl2->getRedirect('http://dx.doi.org/'.$doi);
				foreach($data->author as $author){
					$_SESSION[$this->formId]['data']['people'][] = ['person_id'=>'quickadd', 'name'=>$author->given.' '.$author->family];
				}
				foreach(['published-print', 'published-online', 'issued'] as $date){
					if(!empty($data->$date)){
						$_SESSION[$this->formId]['data']['date'] = implode('-', $data->{$date}->{'date-parts'}[0]);
						break;
					}
				}
			}
		}
	}
	
	/**
	 * Save new publication from edit box over add dataset or add project
	 *
	 * @return void
	 */
	public function saveFromOverlay(){
		$this->version = 1;
		$_SESSION[$this->formId]['data']['publication_version'] = 1;
		$_SESSION[$this->formId]['data']['record_status'] = 'draft';
		$_SESSION[$this->formId]['data']['creator'] = $this->session->userId;
		$this->id = $this->model->insertGeneral($_SESSION[$this->formId]['data']);
		$this->saveKeywords();
		$this->savePeople();
		$this->saveProjects();
		$this->saveDatasets();
		return $this->id;
	}

	/**
	 * Save new or update publication
	 *
	 * @return void
	 */
	protected function doSave(){
		if(\npdc\lib\Args::get('action') === 'new'){
			$this->version = 1;
		}
		if($_SESSION[$this->formId]['db_action'] === 'insert'){
			$_SESSION[$this->formId]['data']['publication_version'] = $this->version;
			$_SESSION[$this->formId]['data']['record_status'] = 'draft';
			$_SESSION[$this->formId]['data']['publication_id'] = $this->id;
			$_SESSION[$this->formId]['data']['creator'] = $this->session->userId;
			$this->id = $this->model->insertGeneral($_SESSION[$this->formId]['data']);
			$saved = true;
		} else {
			$saved = $this->model->updateGeneral($_SESSION[$this->formId]['data'], $this->id, $this->version) !== false;
		}
		$this->saveKeywords();
		$this->savePeople();
		$this->saveProjects();
		$this->saveDatasets();
		
		$_SESSION['notice'] = $saved 
			? '<p>Your changes have been saved.</p>' 
			: 'Something went wrong when trying to save your record';
		if($saved){
			unset($_SESSION[$this->formId]);
			header('Location: '.BASE_URL.'/publication/'.$this->id.'/'.$this->version);
			echo 'redirect';
			die();
		}
	}
	
	/**
	 * Save keywords
	 *
	 * @return void
	 */
	private function saveKeywords(){
		$currentKeywords = $this->model->getKeywords($this->id, $this->version);
		$words = [];
		foreach($currentKeywords as $row){
			$words[] = $row['keyword'];
		}
		$new = array_diff($_SESSION[$this->formId]['data']['keywords'], $words);
		$old = array_diff($words, $_SESSION[$this->formId]['data']['keywords']);
		if(count($old) > 0){
			foreach($old as $word){
				$this->model->deleteKeyword($word, $this->id, $this->version-1);
			}
		}
		if(count($new) > 0){
			foreach($new as $word){
				$this->model->insertKeyword($word, $this->id, $this->version);
			}
		}
	}
	
	/**
	 * Link (bi-directional) to project
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
						'publication_id'=>$this->id, 
						'project_id'=>$_SESSION[$this->formId]['data'][$key],
						'project_version_min'=>$projectModel->getById($_SESSION[$this->formId]['data'][$key])['project_version'] ?? 1,
						'publication_version_min'=>$this->version
					];
					$this->model->insertProject($data);
				}
			}
		}
		$v = $this->version-1;
		$this->model->deleteProject($this->id, $v, $projects);
	}

	/**
	 * Link (bi-directional) to datasets
	 *
	 * @return void
	 */
	private function saveDatasets(){
		$datasets = [];
		$loopId = 'datasets_dataset_id_';
		$projectModel = new \npdc\model\Dataset();
		
		foreach(array_keys($_SESSION[$this->formId]['data']) as $key){
			if(substr($key, 0, strlen($loopId)) === $loopId){
				$datasets[] = $_SESSION[$this->formId]['data'][$key];
				if(strpos($key, '_new_') !== false){
					$data = [
						'publication_id'=>$this->id, 
						'dataset_id'=>$_SESSION[$this->formId]['data'][$key],
						'dataset_version_min'=>$projectModel->getById($_SESSION[$this->formId]['data'][$key])['project_version'] ?? 1,
						'publication_version_min'=>$this->version
					];
					$this->model->insertDataset($data);
				}
			}
		}
		$v = $this->version-1;
		$this->model->deleteDataset($this->id, $v, $datasets);
	}

	/**
	 * Link people
	 *
	 * @return void
	 */
	private function savePeople(){
		$persons = [];
		$loopId = 'people_person_id_';
		$sort = 1;
		foreach(array_keys($_SESSION[$this->formId]['data']) as $key){
			if(substr($key, 0, strlen($loopId)) === $loopId){
				$serial = substr($key, strlen($loopId));
				$id = $_SESSION[$this->formId]['data']['people_publication_person_id_'.$serial];
				$person_id = $_SESSION[$this->formId]['data'][$key];
				$data = ['publication_id'=>$this->id];
				if(empty($person_id) || $person_id === 'quickadd'){
					$data['free_person'] = $_SESSION[$this->formId]['data']['people_name_'.$serial];
					if(empty($data['free_person'])){
						continue;
					}
				} else {
					$data['person_id'] = $person_id;
				}
				$data['organization_id'] = $_SESSION[$this->formId]['data']['people_organization_id_'.$serial];
				$data['editor'] = !empty($_SESSION[$this->formId]['data']['people_editor_'.$serial]) ? 1 : 0;
				$data['sort'] = $sort;
				if(empty($id)){
					$data = array_merge($data, ['publication_version_min'=>$this->version]);
					$persons[] = $this->model->insertPerson($data);
				} else {
					$persons[] = $this->model->updatePerson($id, $data, $this->version);
				}
				$sort++;
			}
		}
		$v = $this->version-1;
		return $this->model->deletePerson($this->id, $v, $persons) !== false;
	}
}