<?php

/**
 * global contact page and personal contact forms
 */

namespace npdc\view;

class Register extends Base{
	public $title = 'Create account';
	public $mid;
	public $right;
	public $class = 'login';
	public $template = 'plain';
	
	private $session;
	private $args;
	private $controller;
	
	/**
	 * 
	 * @param \npdc\lib\login $session
	 * @param array $args
	 * @param \npdc\controller\Contact $controller instance of contact controller
	 */
	public function __construct($session, $args, $controller){
		$this->session = $session;
		$this->args = $args;
		$this->controller = $controller;
	}
	
	/**
	 * the general contact form
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
	 * alias of showList
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
