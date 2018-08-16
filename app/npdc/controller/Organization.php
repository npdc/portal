<?php

/**
 * Organization controller
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\controller;

class Organization extends Base {
	
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
			header('Location: '.BASE_URL);
			die();
		}
		switch(count($args)){
			case 1:
				unset($_SESSION[$this->formId]['data']);
				break;
			case 2:
				if($args[1] !== 'new'){
					break;
				}
			case 3:
			default:
				$id = $args[1];
				$this->display = 'edit_form';
				$this->formId = 'organization_'.$id;
				unset($_SESSION[$this->formId]['data']);
				$this->formController = new \npdc\controller\Form($this->formId);
				$this->formController->getForm('organization');
				$this->formController->form->action = $_SERVER['REQUEST_URI'];
				$this->formController->form->fields->address->fields->country_id->options = $this->getCountries();
				$this->model = new \npdc\model\Organization();
				if(array_key_exists('formid', $_POST)){
					$this->formController->doCheck();
					if($this->formController->ok){
						$data = $_SESSION[$this->formId]['data'];
						foreach($data as $key=>$value){
							if(substr($key, 0, 8) === 'address_'){
								$data[substr($key, 8)] = $data[$key];
								unset($data[$key]);
							}
						}
						if($args[1] === 'new'){
							$id = $this->model->insertOrganization($data);
						} else {
							$id = $args[1];
							$this->model->updateOrganization($data, $args[1]);
						}
						$_SESSION['notice'] = 'The changes have been saved';
						header('Location: '.BASE_URL.'/organization/'.$id);
						die();
					}
					$_SESSION[$this->formId]['data'] = $_POST;
				} elseif($args[1] === 'new'){
					$_SESSION[$this->formId]['data']['country_id'] = 'NL';
				} else {
					$_SESSION[$this->formId]['data'] = $this->model->getById($id);
				}
		}
	}
	
	
}
