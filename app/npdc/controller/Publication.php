<?php

namespace npdc\controller;

class Publication extends Base{
	public $formId = 'publicationlist';
	public $name = 'publication';
	public $userLevelAdd = NPDC_EDITOR;//minimum user level required to add a new dataset
	
	public function __construct($session, $args, $limited = false){
		$this->session = $session;
		$this->args = $args;
		$this->model = new \npdc\model\Publication();
		if(!$limited){
			parent::__construct();
		}
	}
	
	public function recordChanged($id, $version){
		$changed = $this->generalHasChanged($id, $version);
		$tables = ['publication_keyword', 'publication_person', 'project_publication', 'dataset_publication'];
		foreach($tables as $table){
			$changed = $this->tblHasChanged($table, $id, $version) || $changed;
		}
		return $changed;
	}

	protected function alterFields(){
		$this->formController->form->fields->people->fields->organization_id->options = $this->getOrganizations();
	}
	
	protected function loadForm($baseData){
		if($this->id === 'new'){
			unset($_SESSION[$this->formId]);
			$_SESSION[$this->formId]['data']['people'][] = [
				'person_id'=>$this->session->userId, 
				'name'=>$this->session->name, 
				'organization_id'=>$this->session->organization_id, 
				'editor'=>true, 
				'contact'=>true
			];
		} else {
			$_SESSION[$this->formId]['data'] = $baseData;

			$keywords = $this->model->getKeywords($this->id, $this->version);
			$words = [];
			foreach($keywords as $keyword){
				$words[] = $keyword['keyword'];
			}
			$_SESSION[$this->formId]['data']['location_url'] = $baseData['url'];
			$_SESSION[$this->formId]['data']['keywords'] = $words;

			$_SESSION[$this->formId]['data']['people'] = $this->model->getPersons($this->id, $this->version);
			$_SESSION[$this->formId]['data']['date'] = [$baseData['date']];
			if(!empty($baseData['file_id'])){
				$fileModel = new \npdc\model\File();
				$fileData = $fileModel->getFile($baseData['file_id']);
				$_SESSION[$this->formId]['data']['file'] = [$fileData['name'], $fileData['file_id']];
			}
			$_SESSION[$this->formId]['data']['projects'] = $this->model->getProjects($this->id, $this->version, false);
			$_SESSION[$this->formId]['data']['datasets'] = $this->model->getDatasets($this->id, $this->version, false);
		}
	}
	
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


	protected function doSave(){
		if($this->args[1] === 'new'){
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

	private function savePeople(){
		$persons = [];
		$loopId = 'people_person_id_';
		$sort = 1;
		foreach(array_keys($_SESSION[$this->formId]['data']) as $key){
			if(substr($key, 0, strlen($loopId)) === $loopId){
				$persons[] = $_SESSION[$this->formId]['data'][$key];
				$record = [
					'publication_id'=>$this->id, 
					'person_id'=>$_SESSION[$this->formId]['data'][$key]
				];
				$data = [];
				$data['organization_id'] = $_SESSION[$this->formId]['data']['people_organization_id_'.substr($key, strlen($loopId))];
				$data['editor'] = !empty($_SESSION[$this->formId]['data']['people_editor_'.substr($key, strlen($loopId))]) ? 1 : 0;
				$data['sort'] = $sort;
				if(strpos($key, '_new_') === false){
					$saved = $this->model->updatePerson($record, $data, $this->version) === false ? false : $saved;
				} else {
					$data = array_merge($data, $record, ['publication_version_min'=>$this->version]);
					$this->model->insertPerson($data);
				}
				$sort++;
			}
		}
		$v = $this->version-1;
		return $this->model->deletePerson($this->id, $v, $persons) !== false;
	}
}