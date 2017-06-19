<?php

/**
 * global contact page and personal contact forms
 */

namespace npdc\view;

class Contact extends Base{
	public $title = 'Contact';
	public $mid;
	public $right;
	public $class = 'contact';
	
	protected $session;
	protected $args;
	protected $controller;
	protected $userLevelAdd = NPDC_ADMIN;//minimum user level required to add a new person
	
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
		$this->baseUrl = 'person/'.$args[1];
	}
	
	/**
	 * display contact form
	 * @param string $title
	 * @param string $head header above form
	 */
	private function showForm($title, $head){
		if(isset($_SESSION[$this->controller->formId]['state']) && $_SESSION[$this->controller->formId]['state'] === 'sent'){
			$this->mid = 'Thank you for your message. A copy has just been sent to your mailbox.';
			unset($_SESSION[$this->controller->formId]);
		} else {
			$formView = new \npdc\view\Form($this->controller->formId);
			$this->mid = '<h4>'.$title.'</h4>'
				. '<p>'.$head.'</p>'
				. $formView->create($this->controller->form);
		}
	}
	
	/**
	 * the general contact form
	 */
	public function showList(){
		$this->showForm('Send us a message', 'Through this form you can send a message to our general mailbox. You can also send mails to individual people by clicking on their names.');
		$controller = new \npdc\controller\Page($this->session, $this->args);
		$pageView = new \npdc\view\Page($this->session, $this->args, $controller);
		$pageView->showItem('npdc');
		$this->right = $pageView->right;
	}

	/**
	 * a contact form for a specific person
	 */
	public function showItem(){
		$personView = new \npdc\lib\Person();
		//$this->canEdit = isset($this->session->userId) && ($this->session->userLevel === NPDC_ADMIN);
		if(empty($this->controller->person['mail'])) {
			$this->mid = 'No mail address available for '.$this->controller->person['name'];
		} else {
			$this->showForm('Send a message', 'Through this form you can send a message to '.$this->controller->person['name']);
		}
		$this->right = $personView->showPerson($this->controller->person, false).$personView->showAddress($this->controller->person).(empty($this->controller->person['orcid']) ? '' : '<section class="inline"><h4>ORCID</h4><p>'.$this->controller->person['orcid'].'</p></section>');
		
		$projects = $this->controller->model->getProjects($this->controller->person['person_id']);
		$publications = $this->controller->model->getPublications($this->controller->person['person_id']);
		$datasets = $this->controller->model->getDatasets($this->controller->person['person_id']);
		$this->title = 'Contact - '.$this->controller->person['name'];
		if(count($projects) + count($publications) + count($datasets) > 0){
			$this->class .= ' single-col';
			$this->form = '<div class="contactwrapper"><h3>Contact form</h3><div class="contactform">'.$this->mid.'</div></div>';
			$this->right .= $this->form;
			$this->mid = '';
			if(count($datasets) > 0){
				$this->mid .= '<h3>Datasets</h3>';
				$this->mid .= $this->displayTable('dataset', $datasets, ['title'=>'Title', 'date_start'=>'Start date', 'date_end'=>'End date'], ['dataset', 'dataset_id']);
			}
			if(count($publications) > 0){
				$publicationModel = new \npdc\model\Publication();
				foreach($publications as $id=>$publication){
					$publications[$id]['authors'] = $publicationModel->getAuthors($publication['publication_id'], $publication['publication_version']);
				}
				$this->mid .= '<h3>Publications</h3>';
				$this->mid .= $this->displayTable('publication', $publications, ['authors'=>'Authors',
					'title'=>'Title', 
					'year'=>'Year', 
					'journal'=>'Source'], ['publication', 'publication_id']);
			}
			if(count($projects) > 0){
				$this->mid .= '<h3>Projects</h3>';
				$this->mid .= $this->displayTable('project', $projects, ['nwo_project_id'=>'Project id', 'title'=>'Title', 'date_start'=>'Start date', 'date_end'=>'End date'], ['project', 'project_id']);
			}
		}
	}
}
