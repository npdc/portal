<?php

/**
 * Organization controller
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\controller;

class Organization extends Base {
	public $formId = 'organizationlist';
	public $userLevelAdd = NPDC_EDITOR;//minimum user level required to add a new organization
	/**
	 * Constructor
	 *
	 * @param object $session login information
	 *
	 */
	public function __construct($session){
		$this->session = $session;
		if(\npdc\lib\Args::get('action') === 'new' && $this->session->userLevel < $this->userLevelAdd){
			return;
		} elseif (\npdc\lib\Args::exists('action')){
			$id = \npdc\lib\Args::get('id');
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
					if(\npdc\lib\Args::get('action') === 'new'){
						$id = $this->model->insertOrganization($data);
					} else {
						$id = \npdc\lib\Args::get('id');
						$this->model->updateOrganization($data, \npdc\lib\Args::get('id'));
					}
					$_SESSION['notice'] = 'The changes have been saved';
					header('Location: '.BASE_URL.'/organization/'.$id);
					die();
				}
				$_SESSION[$this->formId]['data'] = $_POST;
			} elseif(\npdc\lib\Args::get('action') === 'new'){
				$_SESSION[$this->formId]['data']['country_id'] = 'NL';
			} else {
				$_SESSION[$this->formId]['data'] = $this->model->getById($id);
			}
		} elseif(!\npdc\lib\Args::exists('id')) {
			unset($_SESSION[$this->formId]['data']);
			$this->formController = new \npdc\controller\Form($this->formId);
			$this->formController->getForm('organizationlist');
			if($this->session->userLevel >= NPDC_ADMIN){
				$this->formController->form->fields->country->options = $this->getCountries(false);
			} else {
				$this->formController->form->fields->country->disabled = true;
			}
			
			if(!array_key_exists('formid', $_GET)){
				$_GET = array_merge(['formid'=>$this->formId], \npdc\config::$defaultOrganizationFilter);
			}
			if(array_key_exists('formid', $_GET)){
				$this->formController->doCheck('get');
			}
			if($this->session->userLevel < NPDC_ADMIN){
				$_SESSION[$this->formId]['data']['country'] = \npdc\config::$defaultOrganizationFilter['country'];
			}
		}
	}	
}
