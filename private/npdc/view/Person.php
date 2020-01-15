<?php

/**
 * Person view, used for admins
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\view;

class Person extends Base{
	public $title = 'People';
	public $mid;
	public $right;
	public $class = 'page';
	public $accessLevel;
	public $canEdit;
	public $baseUrl;
	public $takeover;
	protected $session;
	protected $controller;
	protected $model;
	
	/**
	 * Constructor
	 *
	 * @param object $session login information
	 *
	 * @param object $controller person controller
	 */
	public function __construct($session, $controller){
		$this->session = $session;
		$this->controller = $controller;
		$this->canEdit = $session->userLevel >= NPDC_ADMIN;
		$this->baseUrl = $controller->id;
		$this->takeover = $session->userLevel === NPDC_ADMIN;
		
		$this->model = new \npdc\model\Person();
		$this->baseUrl = \npdc\lib\Args::getBaseUrl();
	}
	
	/**
	 * Show list of persons
	 *
	 * @return void
	 */
	public function showList(){
		$this->class = 'list';
		$list = $this->model->getList(isset($_SESSION[$this->controller->formId]['data'])
				? $_SESSION[$this->controller->formId]['data'] 
				: null
			);
		$this->left = parent::showFilters('personlist');
		$list2 = [];
		$list = array_values($list);
		$n = count($list);
		$page = \npdc\lib\Args::get('page') ?? 1;
		for($i = ($page-1)*\npdc\config::$rowsPerPage; $i < min($page*\npdc\config::$rowsPerPage, $n); $i++){
			$person = $list[$i];
			$person['report'] = '<button onclick="javascript:event.stopPropagation();location.href=\''.BASE_URL.'/person/'.$person['person_id'].'/report\'">View</button>';
			$person['takeover'] = '<button onclick="javascript:event.stopPropagation();location.href=\''.BASE_URL.'/person/'.$person['person_id'].'/takeover\'">Take over</button>';
			$list2[] = $person;
		}
		$this->makePager($n, $page);
		$this->mid = $this->displayTable('person searchbox', $list2
			, ['name'=>'Name',
				'organization_name'=>'Organization',
				'report'=>'Content',
				'user_level' =>'Role',
				'takeover'=>'Rights']
			, ['person', 'person_id']);
		$this->canEdit = false;
	}
	
	/**
	 * Show person details
	 *
	 * @param integer $id person id
	 * @return void
	 */
	public function showItem($id){
		$this->canEdit = isset($this->session->userId) 
			&& ($this->session->userLevel === NPDC_ADMIN);
		if($id === 'new'){
			$this->title = 'Add person';
		} else {
			$person = $this->model->getById($id);
			$this->title = $person['name'];
		}
		if(($this->canEdit && \npdc\lib\Args::get('action') === 'edit') || \npdc\lib\Args::get('action') === 'new'){
			$this->loadEditPage();
		} elseif(\npdc\lib\Args::get('action') === 'report') {
			$this->data = $person;
			$this->mid = parent::parseTemplate('person_report');
			$this->class .= ' report';
		} elseif(\npdc\lib\Args::get('action') === 'takeover') {
			$this->data = $person;
			$this->mid = '<p>Are you sure you want to take over the rights of '.$person['name'].'?<br/>Anything you do will be logged under the name of '.$person['name'].'.</p><p><strong>Remember: </strong> With great power comes great responsibility!</p><button onclick="location.href=\''.BASE_URL.'/person/'.$person['person_id'].'/takeover/do\'">Yes, do take over rights</button>';
			$this->class .= ' report';
		} else {
			$this->data = $person;
			$this->mid = '<a href="'.$_SERVER['REQUEST_URI'].'/report">Content of person</a>'.parent::parseTemplate('person_mid');
			$this->right = '<a href="'.BASE_URL.'/organization/'.$person['organization_id'].'">View organization</a>'.parent::parseTemplate('person_right');
		}
	}
}
