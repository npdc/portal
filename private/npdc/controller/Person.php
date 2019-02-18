<?php
/**
 * Person controller
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\controller;

class Person extends Base {
	public $formId = 'personlist';
	
	/**
	 * Constructor
	 *
	 * @param object $session login information
	 * @param array $args url parameters
	 */
	public function __construct($session, $args){
		$this->session = $session;
		$this->args = $args;
		if($session->userLevel < NPDC_ADMIN){
			header('Location: '.BASE_URL.'/');
			die();
		}
		if($args['action'] === 'new' && $this->session->userLevel < $this->userLevelAdd){
			return;
		} elseif (array_key_exists('id', $this->args) || array_key_exists('action', $this->args)){
			$id = $args['id'];
			$this->display = 'edit_form';
			$this->formId = 'person_'.$id;
			unset($_SESSION[$this->formId]['data']);
			$this->formController = new \npdc\controller\Form($this->formId);
			$this->formController->getForm('person');
			$this->formController->form->fields->organization_id->options = $this->getOrganizations();
			$this->formController->form->fields->user_level->disabled = false;
			$this->formController->form->action = $_SERVER['REQUEST_URI'];
			$this->model = new \npdc\model\Person();
			if(array_key_exists('formid', $_POST)){
				$this->formController->doCheck();
				if($this->formController->ok){
					if($this->model->checkMail($_SESSION[$this->formId]['data']['mail'], $args['action'] === 'new' ? 0 : $args['id'])){
						unset($_SESSION[$this->formId]['data']['formid']);
						unset($_SESSION[$this->formId]['data']['submit']);
						$data = $_SESSION[$this->formId]['data'];
						if($args['action'] === 'new'){
							$id = $this->model->insertPerson($data);
						} else {
							$id = $args['id'];
							$this->model->updatePerson($data, $args['id']);
						}
						$_SESSION['notice'] = 'The changes have been saved';
						header('Location: '.BASE_URL.'/person/'.$id);
					} else {
						$_SESSION[$this->formId]['errors']['mail'] = 'There is another person with this mail address';
					}
				}
				$_SESSION[$this->formId]['data'] = $_POST;
			} elseif($args['action'] !== 'new'){
				$_SESSION[$this->formId]['data'] = $this->model->getById($id);
			}
		} else {
			unset($_SESSION[$this->formId]['data']);
			$this->formController = new \npdc\controller\Form($this->formId);
			$this->formController->getForm('personlist');
			$this->formController->form->fields->organization->options = $this->getOrganizations('person');
			
			if(array_key_exists('formid', $_GET)){
				$this->formController->doCheck('get');
			}
		}
	}
}