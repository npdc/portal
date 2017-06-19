<?php

namespace npdc\view;

class Account extends Base {
	public $class = 'detail';
	public $right = '<ul>'
			. '<li><a href="'.BASE_URL.'/account">View account</a></li>'
			. '<li><a href="'.BASE_URL.'/account/edit">Edit details</a></li>'
			. '<li><a href="'.BASE_URL.'/account/password">Change password</a></li>'
			. '</ul>';
	
	public function __construct($session, $args, $controller){
		$this->session = $session;
		$this->args = $args;
		$this->controller = $controller;
	}
	
	public function showList(){
		$this->title = 'Account';
		$this->model = new \npdc\model\Person();
		$this->data = $this->model->getById($this->session->userId);
		$this->mid = parent::parseTemplate('person_mid');
		$this->right .= parent::parseTemplate('organization_mid');
	}
	
	public function showItem($item){
		switch ($item){
			case 'edit':
				$this->title = 'Edit details';
				break;
			case 'password':
				$this->title = 'Edit password';
		}
		$this->loadEditPage();
		$this->class = 'detail edit';
	}
}