<?php

namespace npdc\controller;

/**
 * Account (works based on person table)
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

class Account extends Base{
	public $formId = 'account';
	private $person;
	
	/**
	 * Constructor
	 *
	 * @param object $session login information
	 * @param array $args url parameters
	 */
	public function __construct($session, $args) {
		if($session->userLevel < NPDC_USER){
			header('Location: '.BASE_URL.'/');
			die();
		}
		$this->session = $session;
		$this->args = $args;
		$this->model = new \npdc\model\Person();
		if(count($args) > 1){
			$this->formController = new \npdc\controller\Form($this->formId);
			$this->person = $this->model->getById($this->session->userId);
			switch($args[1]){
				case 'edit':
					$this->formController->getForm('person');
					$this->formController->form->fields->organization_id->options = $this->getOrganizations();
					$_SESSION[$this->formId]['data'] = $this->person;
					break;
				case 'password':
					$this->formController->getForm('change_password');
				
			}
			$this->formController->form->action = $_SERVER['REQUEST_URI'];
		}
		if(array_key_exists('formid', $_POST)){
			$this->formController->doCheck();
			if($this->formController->ok){
				switch($args[1]){
					case 'edit':
						$data = $_SESSION[$this->formId]['data'];
						$cur = $this->model->getUser($data['mail']);
						if($cur['person_id'] !== $this->session->userId){
							$_SESSION[$this->formId]['errors']['mail'] = 'There is alreay an account with this mail address.';
						} else {
							unset($data['user_level']);
							$this->model->updatePerson($data, $this->session->userId);
							$_SESSION['notice'] = 'The changes have been saved';
							header('Location: '.BASE_URL.'/account');
						}
						break;
					case 'password':
						if(!password_verify($_POST['current'], $this->person['password'])){
							$this->formController->ok = false;
							$_SESSION[$this->formId]['errors']['current'] = 'Password not correct';
						} else {
							if(strlen($_POST['new']) < \npdc\config::$passwordMinLength){
								$_SESSION[$this->formId]['errors']['new'] = 'New password too short, should be at least '.\npdc\config::$passwordMinLength.' characters';	
							} else {
	//check new password for complexity and do save
								$data = ['password'=> password_hash($_POST['new'], PASSWORD_DEFAULT)];
								$this->model->updatePerson($data, $this->session->userId);
								$_SESSION['notice'] = 'The changes have been saved';
								header('Location: '.BASE_URL.'/account');
							}
						
						}
				}
			}			
		}
	}
}