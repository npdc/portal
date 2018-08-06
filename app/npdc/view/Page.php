<?php

/**
 * display basic pages
 */

namespace npdc\view;

class Page extends Base{
	public $title;
	public $mid;
	public $right;
	public $class = 'page';
	public $accessLevel;
	public $canEdit;
	public $baseUrl;
	private $session;
	private $args;
	protected $controller;
	
	/**
	 * 
	 * @param object $session
	 * @param array $args
	 */
	public function __construct($session, $args, $controller){
		$this->session = $session;
		$this->args = $args;
		$this->controller = $controller;
		$this->canEdit = $session->userLevel >= NPDC_ADMIN;
		$this->baseUrl = $controller->id;
	}
	
	/**
	 * display 404 error page
	 */
	public function errorPage(){
		$this->title = 'Page not found';
		$this->class = 'page';
		$this->left = null;
		$this->right = null;
		http_response_code(404);
		$this->mid = 'The page you requested could not be found.';
	}
	
	/**
	 * no list available, show error instead, should never be called. Only present for compatibility
	 */
	public function showList(){
		$this->errorPage();
	}
	
	/**
	 * get a page
	 * @param string $page url of the page
	 */
	public function showItem($page){
		$model = new \npdc\model\Page();

		$data = $model->getByUrl($page);
		
		if($data === false){
			$this->errorPage();
		} else {
			$this->title = $data['title'];
			
			switch($this->controller->display){
				case 'edit':
					$this->loadEditPage();
					break;
				case 'not_allowed':
					$this->title = 'No access';
					$this->mid = 'You don\'t have access to this page';
					break;
				default:
					$this->mid = '';
					if($data['show_last_revision']){
						$this->mid = '<p class="last-rev">Last revision: '.date('j F Y', strtotime($data['last_update'])).'</p>';
					}
					$this->mid .= preg_replace('#\<(a href|img src)="((?!.*(:\/\/)).*)?"#', '<$1="'.BASE_URL.'/$2"', $data['content']);

					$persons = $model->getPersons($data['page_id']);

					if(count($persons) > 0){
						$this->displayPersons($persons);

					}
					$urls = $model->getUrls($data['page_id']);
					if(count($urls) > 0){
						$this->displayUrls($urls);
					}
			}
		}
	}
	
	/**
	 * display list of persons
	 * @param array $persons result of getPersons
	 */
	private function displayPersons($persons){
		$personView = new \npdc\lib\Person();
		foreach($persons as $person){
			//display address if person of different organization than previous person
			$this->right .= '<div><h4>'.$person['role'].'</h4>'
					.$personView->showPerson($person).'</div>';
		}
	}
	
	/**
	 * display list of urls
	 * @param type $urls result of getUrls
	 */
	private function displayUrls($urls){
		$this->right .= '<div><h4>Links</h4><ul>';
		foreach($urls as $url){
			$this->right .= '<li><a href="'.$url['url'].'">'.$url['text'].'</a></li>';
		}
		$this->right .= '</ul></div>';
	}	
}
