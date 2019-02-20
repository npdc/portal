<?php

/**
 * registration page
 */

namespace npdc\view;

class Register extends Base{
	public $title = 'Create account';
	public $mid;
	public $right;
	public $class = 'login';
	public $template = 'plain';
	
	private $session;
	private $controller;
	
	/**
	 * Constructor
	 *
	 * @param object $session login information
	 *
	 * @param object $controller registration controller
	 */
	public function __construct($session, $controller){
		$this->session = $session;
		$this->controller = $controller;
		$this->extraHeader = '<meta name="robots" content="noindex">';
	}
	
	/**
	 * show registration form
	 * 
	 * request account creation link
	 * 
	 * @return void
	 */
	public function showList(){
		if(array_key_exists('formid', $_POST) && $this->controller->formId === 'register' && $this->controller->formController->ok){
			$this->title = '';
		} else {
			$formView = new \npdc\view\Form($this->controller->formId);
			$this->mid = $formView->create($this->controller->form);
		}
	}

	/**
	 * show create account form
	 * 
	 * @return void
	 */
	public function showItem($id){
		$this->template = 'page';
		if(empty($this->controller->formId)) {
			$this->title = 'Link not valid';
			$this->mid = 'This link is not valid, either it is older than '.\npdc\config::$resetExpiryHours.' hours, already used or it didn\'t exist at all.';
			return;
		} else {
			$this->title = 'Set password';
		}
		$this->showList();
	}
}
