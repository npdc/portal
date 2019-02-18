<?php

/**
 * the view for search
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\view;

class Search extends Base{
	public $title = 'Search';
	public $left;
	public $mid;
	public $right;
	public $class = 'list search';
	public $accessLevel;
	protected $session;
	protected $args;
	protected $controller;
	protected $userLevelAdd = NPDC_NOBODY;//minimum user level required to add a new project
	private $search;
	private $types;
	private $type = [];
	
	/**
	 * Constructor
	 *
	 * @param object $session login information
	 * @param array $args url parameters
	 * @param object $controller account controller
	 */
	public function __construct($session, $args, $controller){
		$this->session = $session;
		$this->args = $args;
		$this->controller = $controller;
		if(array_key_exists('types', $args)){
			$this->type = explode('+', $args['types']);
		}
		
		$this->search = str_replace('+', ' ', $this->args['subject']);
		$_SESSION[$this->controller->formId]['data']['q'] = $this->search;
		$_SESSION[$this->controller->formId]['data']['type'] = $this->type;
	}
	
	/**
	 * gets the search form
	 * 
	 * @return string formatted form
	 */
	public function getForm(){
		$formView = new \npdc\view\Form($this->controller->formId);
		$this->types = get_object_vars($this->controller->form->fields->type->options);
		if(count(get_object_vars($this->controller->form->fields->type->options)) < 2){
			unset($this->controller->form->fields->type);
		}
		return $formView->create($this->controller->form, false);
	}
	
	/**
	 * shows the search results
	 * 
	 * @return void
	 */
	public function showList(){
		$this->left = $this->getForm();
		if(!isset($this->search) || is_null($this->search) || strlen($this->search) === 0){
			$this->mid = 'Please provide a search term';
		} else {
			$list = [];

			//first check if result is uuid and uuid exists
			if(\Lootils\Uuid\Uuid::isValid($this->search)){
				foreach(array_keys($this->types) as $type){
					$modelName = 'npdc\\model\\'.ucfirst($type);
					$model = new $modelName();
					$res = $model->getByUUID($this->search);
					if($res !== false){
						header('Location: '.BASE_URL.'/'.$res['uuid']);
						die();
					} else {
						$this->mid = 'No result for uuid '.$this->search;
					}
				}
			} else {
				//search organizations
				$orgModel = new \npdc\model\Organization();
				$orgFilter = [
					'country'=>\npdc\config::$defaultOrganizationFilter['country'],
					'type'=>['project','dataset','publication'],
					'combine'=>'any',
					'search'=>$this->search
				];
				$orgs = $orgModel->getList($orgFilter);
				if(count($orgs) > 0){
					$this->mid .= count($orgs).' Organization'.(count($orgs) > 1 ? 's' : '').' found<ul>';
					foreach ($orgs as $org){
						$this->mid .= '<li><a href='.BASE_URL.'/organization/'.$org['organization_id'].'>'.$org['organization_name'].'</a></li>';
					}
					$this->mid .= '</ul>';
				}

				//free text search trough dataset, project and publication
				foreach(count($this->type) === 0 ? array_keys($this->types) : $this->type as $type){
					$modelName = 'npdc\\model\\'.ucfirst($type);
					$model = new $modelName();
					$res = $model->search($this->search, true);
					if(!is_null($res)){
						$list = array_merge($list, $res);
					}
				}

				//make keys with date for ordering by date
				$keys = [];
				foreach($list as $data){
					$key = $data['date'];
					$i = 0;
					while(in_array($key, $keys)){
						$i++;
						$key = $data['date'].' '.$i;
					}
					$keys[] = $key;
				}
				$list = array_combine($keys, $list);
				krsort($list);

				//Display results
				$this->mid .= count($list).' result'.(count($list) === 1 ? '' : 's').' for \''.$this->search	.'\'';
				$arr = count($this->type) > 0 ? $this->type : array_keys($this->types);
				$this->mid .= ' in ';
				foreach($arr as $i=>$type){
					if($i>0 && $i<count($arr)-1){
						$this->mid .= ', ';
					} elseif ($i>0){
						$this->mid .= ' and ';
					}
					$this->mid .= $this->types[$type];
				}
				if(count($list) > 0){
					$this->mid .= $this->displayTable('search', $list, ['content_type'=>'Type', 'title'=>'Title', 'date'=>'Date'], ['content_type', 'content_type_id'], false);
				}
			}	
		}
	}
	
	/**
	 * alias of showList
	 * 
	 * @return void
	 */
	public function showItem(){
		$this->showList();
	}
}
