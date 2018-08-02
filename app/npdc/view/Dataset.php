<?php

namespace npdc\view;

class Dataset extends Base{
	public $title;
	public $left;
	public $mid;
	public $right;
	public $class;
	public $accessLevel;
	protected $session;
	protected $args;
	protected $controller;
	public $canEdit = false;
	public $baseUrl;
	public $versions;
	
	public function __construct($session, $args, $controller){
		$this->session = $session;
		$this->args = $args;
		$this->controller = $controller;
		$this->baseUrl = implode('/', array_slice($args, 0, 2));
		$this->model = new \npdc\model\Dataset();
		parent::__construct();
	}
	
	public function listStatusChanges(){
		$version = $this->version;
		if(count($this->args) > 2){
			if(in_array($this->args[2], ['edit', 'submit'])){
				$version = $this->versions[0]['dataset_version'];
			} elseif(is_numeric($this->args[2])){
				$version = $this->args[2];
			} else {
				foreach($this->versions as $version){
					if($version['record_status'] === $this->args[2]){
						$version = $version['dataset_version'];
						break;
					}
				}
			}
		}
		return $this->doListStatusChanges($this->args[1], $version);
	}
	
	public function showList(){
		$this->left = parent::showFilters($this->controller->formId);
		$list = $this->model->getList(isset($_SESSION[$this->controller->formId]['data']) 
				? $_SESSION[$this->controller->formId]['data'] 
				: null
			, true);
		if(NPDC_OUTPUT === 'xml'){
			$this->xml =  new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><datasets></datasets>');
			$fields = ['uuid','dataset_id', 'dataset_version', 'title', 'published', 'url'];
			foreach($list as $dataset){
				$em = $this->xml->addChild('dataset');
				foreach($fields as $field){
					switch($field){
						case 'url':
							$value = 'http'.(empty($_SERVER['HTTPS'])?'':'s').'://'.$_SERVER['HTTP_HOST'].BASE_URL.'/dataset/'.$dataset['dataset_id'].'.xml';
							break;
						default:
							$value = $dataset[$field];

					}
					$em->addChild($field, html_entity_decode($value));
				}
			}
			header('Content-Type: application/xml');
			$dom = new \DOMDocument('1.0');
			$dom->preserveWhiteSpace = false;
			$dom->formatOutput = true;
			$dom->loadXML($this->xml->asXML());
			echo $dom->saveXML();
			die();
		} else {
			$this->class = 'list dataset';
			$this->title = 'Datasets';
			$this->mid = $this->displayTable('dataset', $list
					, ['title'=>'Title', 
						'date_start'=>'Start date', 
						'date_end'=>'End date']
					, ['dataset', 'dataset_id']
					, true
					, true
				);
		}
	}

	//Display a single dataset
	public function showItem($dataset){
		if(strpos($dataset, '.') !== false){
			list($dataset, $this->args[2]) = explode('.', $dataset);
		}
		if($dataset !== 'new'){
			$this->canEdit = isset($this->session->userId) && ($this->session->userLevel === NPDC_ADMIN || $this->model->isEditor($dataset, $this->session->userId));
			$this->versions = $this->model->getVersions($dataset);

			if(in_array($this->args[2], ['edit', 'submit', 'warnings'])){
				$v = $this->versions[0]['dataset_version'];
			} elseif (count($this->args) > 2 && (is_numeric($this->args[2]))){
				$v = $this->args[2];
			}
			$this->data = isset($v) 
				? $this->model->getById($dataset, $v)
				: $this->model->getById($dataset);
			
			if(!$this->canEdit && !in_array($this->data['record_status'], ['published', 'archived'])){
				$this->data = false;
			}

			if($this->data === false && $this->canEdit){
				$this->data = $this->model->getById($dataset, 1);
			}
			$this->version = $this->data['dataset_version'];
		}

		if($this->data === false && $dataset !== 'new'){//dataset not found
			$this->showError();
		} elseif(NPDC_OUTPUT === 'xml'){//show as xml
			$this->showXml();
		} elseif ((!$this->canEdit || is_null($this->controller->display)) && $dataset !== 'new') {//display dataset
			$this->showDataset();
		} elseif($this->args[2] === 'warnings') {
			$this->title = 'Please check - '.$this->data['title'];
			$this->mid = $this->controller->showWarnings();
		} else {
			$this->title = ($dataset === 'new') ? 'New dataset' : 'Edit dataset - '.$this->data['title'];
			$this->baseUrl .= '/'.$this->versions[0]['dataset_version'];
			$this->loadEditPage($this->controller->pages);
		}
	}


	private function displayDataCenter($organization_id){
		$organization = $this->organizationModel->getById($organization_id);
		$return = [
			'Organization_Type'=>'DISTRIBUTOR',
			'Organization_Name'=>[
				'Short_Name'=>$organization['dif_code'],
				'Long_Name'=>$organization['organization_name']
			],
			'Organization_URL'=>$organization['website']
		];
		return $return;
	}
	
	private function displayDataCenterPersonnel($person_id){
		$person = $this->personModel->getById($person_id);
		$return = [
			'First_Name'=>$person['given_name'] ?? $person['initials'],
			'Last_Name'=>$person['surname']
		];
		$organizationDetail = $this->organizationModel->getById($person['organization_id']);
		foreach([
			'organization_address'=>'Street_Address',
			'organization_city'=>'City',
			'organization_zip'=>'Postal_Code',
			'country_name'=>'Country'
		] as $source=>$target){
			$return['Address'][$target] = $organizationDetail[$source];
		}

		foreach(['personal'=>'Direct Line', 'secretariat'=>'Telephone', 'mobile'=>'Mobile'] as $phoneType=>$label){
			if($person['phone_'.$phoneType.'_public'] === 'yes' && !empty($person['phone_'.$phoneType])){
				$return['Phone'][] = ['Number'=>str_replace(['(0)', ' '], '', $person['phone_'.$phoneType]), 'Type'=>$label];
			}
		}
		$return['Email'] = $person['mail'];
		return $return;
	}

	private function showXml(){
		$this->personModel = new \npdc\model\Person();
		$this->organizationModel = new \npdc\model\Organization();
		include('template/dataset/xml.php');
		die();
	}

	private function showError(){
		if(count($this->versions) === 0){
			$this->title = 'Not found';
			$this->mid .= 'The requested dataset could not be found';
		} elseif(!$this->canEdit){
			if($this->session->userLevel === NPDC_PUBLIC){
				$this->title = 'Please login';
				$this->mid .= 'Please login<script type="text/javascript" language="javascript">$.ready(openOverlay(\''.BASE_URL.'/login?notice=login\'));</script>';
			} else {
				$this->title = 'No access';
				$this->mid .= 'No access';
			}
		} elseif(is_numeric($this->args[2])) {
			$this->title = 'No version '.$this->args[2].' found';
			$this->mid .= 'There is no version '.$this->args[2].' of this dataset.';
		} else {
			$this->title = 'No '.$this->args[2].' version found';
			$this->mid .= 'There is no '.$this->args[2].' version of this dataset.';
		}
	}

	private function showDataset(){
		if($this->canEdit && count($this->versions) > 1){
			$v = $this->data['dataset_version'];
			$_SESSION['notice'] .= 'See version <select id="versionSelect" style="width:auto">';
			foreach($this->versions as $version){
				$_SESSION['notice'] .= '<option value="'.BASE_URL.'/dataset/'.$this->data['dataset_id'].'/'.$version['dataset_version'].'" '
					. (in_array($v, [$version['dataset_version'], $version['record_status']]) ? 'selected=true' : '')
					. '>'.$version['dataset_version'].' - '.$version['record_status'].'</option>';
			}
			$_SESSION['notice'] .= '</select>';
		}
		$changed = $this->controller->recordChanged($this->data['dataset_id'], $this->data['dataset_version']);
		if(!$changed){
			if($this->data['record_status'] === 'draft'){
				$_SESSION['notice'] .= ' Publishing this draft is not possible since it doesn\'t appear to be different than the published record.';
			}
		} elseif($this->args[2] === 'submit' && $this->data['record_status'] === 'draft'){
			$_SESSION['notice'] = $this->controller->submitForm;
		} elseif($this->data['record_status'] !== 'published'){
			if($this->session->userLevel === NPDC_ADMIN && $this->data['record_status'] === 'submitted'){
				if($this->args[2] !== 'submitted'){
					header('Location: '.BASE_URL.'/dataset/'.$this->args[1].'/submitted');
					die();
				}
				$_SESSION['notice'] = $this->controller->publishForm;
			}
			$_SESSION['notice'] .= ' You are looking at a '.$this->data['record_status'].' version of this page'.($this->data['record_status'] === 'draft' ? $this->controller->draftMsg : '');
			if(!$this->canEdit){
				$cur = $this->model->getById($this->data['dataset_id']);
				$_SESSION['notice'] .= ', the current can version can be found <a href="'.BASE_URL.'/'.$cur['uuid'].'">here</a>';
			}
		} 
		$this->title = 'Dataset - '.$this->data['title'].(is_null($this->data['acronym']) ? '' : ' ('.$this->data['acronym'].')');
		$this->class = 'detail';
		if(in_array('files', $this->args)){
			if(in_array('request', $this->args)){
				if($this->session->userLevel === NPDC_PUBLIC){
					header('Location: '.BASE_URL.'/'.implode('/', array_slice($this->args, 0, -1)));
					die();
				}
				$this->mid .= parent::parseTemplate('dataset_files_request_mid');
				$this->right = parent::parseTemplate('dataset_files_right');
			} else {
				$this->mid .= parent::parseTemplate('dataset_files_mid');
				$this->right = parent::parseTemplate('dataset_files_right');
			}
		} else {
			$this->mid .= parent::parseTemplate('dataset_mid');
			$this->right = parent::parseTemplate('dataset_right');
		}
	}
	
	public function showCharacteristics($type, $id, $version){
		$characteristics = $this->model->getCharacteristics($type, $id, $version);
		if(count($characteristics) > 0){
			echo '<h4>Characteristics</h4><table style="width:auto">';
			foreach($characteristics as $characteristic){
				echo '<tr><td>'.$characteristic['name'].':</td><td>'.$characteristic['value'].' '.$characteristic['unit'].'</td></tr>';
			}
			echo '</table>';
		}
	}
}