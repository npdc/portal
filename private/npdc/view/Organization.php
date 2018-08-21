<?php

/**
 * Organization view
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\view;

class Organization extends Base{
	public $title;
	public $mid;
	public $right;
	public $class = 'page';
	public $accessLevel;
	public $canEdit;
	public $baseUrl;
	protected $session;
	private $args;
	protected $controller;
	protected $model;
	
	/**
	 * Constructor
	 *
	 * @param object $session login information
	 * @param array $args url parameters
	 * @param object $controller organization controller
	 */

	public function __construct($session, $args, $controller){
		$this->session = $session;
		$this->args = $args;
		$this->controller = $controller;
		$this->canEdit = $session->userLevel >= NPDC_ADMIN;
		$this->baseUrl = $controller->id;
		
		$this->model = new \npdc\model\Organization();
		$this->baseUrl = implode('/', array_slice($args, 0, 2));
	}
	
	/**
	 * Show list of organizations
	 *
	 * @return void
	 */
	public function showList(){
		$this->class = 'list';
		$this->canEdit = false;
		$this->title = 'Organizations';
		$organizations = $this->model->getList();
		$this->mid = $this->displayTable('organization searchbox', $organizations
			, ['organization_name'=>'Name',
				'organization_city'=>'City',
				'country_name'=>'Country']
			, ['organization', 'organization_id']);
	}
	
	/**
	 * Show organization
	 *
	 * @param integer|string $id organization id or new
	 * @return void
	 */
	public function showItem($id){
		$this->canEdit = isset($this->session->userId) 
			&& ($this->session->userLevel === NPDC_ADMIN);
		if($id === 'new'){
			$this->title = 'Add organization';
		} else {
			$organization = $this->model->getById($id);
			$this->title = $organization['name'];
		}
		if(($this->canEdit && $this->args[2] === 'edit') || $id === 'new'){
			$this->loadEditPage();
		} else {
			$this->data = $organization;
			$this->mid = parent::parseTemplate('organization_mid');
		}
	}
}
