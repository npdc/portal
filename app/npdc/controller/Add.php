<?php

namespace npdc\controller;

/**
 * Add new entries of several types
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */
class Add extends Base{
	
	/**
	 * Constructor
	 *
	 * @param object $session login information
	 * @param array $args url parameters
	 */
	public function __construct($session, $args) {
		$this->session = $session;
		$this->args = $args;
		if($session->userLevel < NPDC_EDITOR){
			header('Location: '.BASE_URL);
			die();
		}
		$this->formId = $args[1].'_new';
		$this->formController = new \npdc\controller\Form($this->formId);
		switch($args[1]){
			case 'person':
				$this->formController->getForm('person');
				$this->formController->form->fields->organization_id->options = $this->getOrganizations();
				break;
			case 'organization':
				$this->formController->getForm('organization');
				$this->formController->form->fields->address->fields->country_id->options = $this->getCountries();
				break;
			case 'publication':
				$this->formController->getForm('publication_general');
				$this->formController->form->fields->people->fields->organization_id->options = $this->getOrganizations();
				$this->formController->form->fields->submit->value = 'Save and add';
				if(in_array($_GET['ref'], ['Dataset', 'Project'])){
					$ref = strtolower($_GET['ref']).'s';
					unset($this->formController->form->fields->{$ref});
				}
				break;
			default:
				header('Location: '.BASE_URL);
				die();		
		}
		$this->formController->form->action = $_SERVER['REQUEST_URI'];
		if(isset($_POST['formid'])){
			$this->formController->doCheck();
			if($this->formController->ok){
				switch($args[1]){
					case 'person':
						$model = new \npdc\model\Person();
						if($model->checkMail($_SESSION[$this->formId]['data']['mail'], 0)){
							$id = $model->insertPerson($_SESSION[$this->formId]['data']);
							$this->return = ['id'=>$id, 'label'=>$_SESSION[$this->formId]['data']['name'], 'nextfield'=>$_SESSION[$this->formId]['data']['organization_id']];
							unset($_SESSION[$this->formId]);
						} else {
							$_SESSION[$this->formId]['errors']['mail'] = 'There is another person with this mail address';
						}
						break;
					case 'organization':
						$model = new \npdc\model\Organization();
						$id = $model->insertOrganization($_SESSION[$this->formId]['data']);
						$this->return = ['id'=>$id, 'label'=>$_SESSION[$this->formId]['data']['organization_name']];
						unset($_SESSION[$this->formId]);
						break;
					case 'publication':
						$publicationController = new \npdc\controller\Publication($this->session, ['publication', 'new'], true);
						$publicationController->formId = $this->formId;
						$id = $publicationController->saveFromOverlay();
						$this->return = ['id'=>$id, 'label'=>$_SESSION[$this->formId]['data']['title']];
						unset($_SESSION[$this->formId]);
				}
			}
		} else {
			switch($args[1]){
				case 'person':
					$_SESSION[$this->formId]['data'] = [];
					$_SESSION[$this->formId]['data']['name'] = urldecode($args[2]);
					break;
				case 'organization':
					$_SESSION[$this->formId]['data'] = [];
					$_SESSION[$this->formId]['data']['organization_name'] = urldecode($args[2]);
					$_SESSION[$this->formId]['data']['country_id'] = 'NL';
					break;
				case 'publication':
					$_SESSION[$this->formId]['data'] = [];
					$_SESSION[$this->formId]['data']['title'] = urldecode($args[2]);
					break;
					
			}
		}
	}
}