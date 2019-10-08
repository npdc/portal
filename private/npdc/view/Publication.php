<?php

/**
 * Publication view
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\view;

class Publication extends Base{
	public $title;
	public $left;
	public $mid;
	public $right;
	public $class;
	public $accessLevel;
	protected $session;
	protected $controller;
	protected $userLevelAdd = NPDC_EDITOR;//minimum user level required to add a new publication
	public $canEdit = false;
	public $baseUrl;
	public $versions;

	/**
	 * Constructor
	 *
	 * @param object $session login information
	 *
	 * @param object $controller account controller
	 */
	public function __construct($session, $controller){
		$this->session = $session;
		$this->controller = $controller;
		$this->baseUrl = \npdc\lib\Args::getBaseUrl();
		$this->model = new \npdc\model\Publication();
		parent::__construct();
	}
	
	/**
	 * List status changes of record
	 *
	 * @return void
	 */
	public function listStatusChanges(){
		$version = $this->version;
		if(\npdc\lib\Args::exists('action') && in_array(\npdc\lib\Args::get('action'), ['edit', 'submit'])){
			$version = $this->versions[0]['publication_version']; 
		} elseif (\npdc\lib\Args::exists('version')){
			$version = \npdc\lib\Args::get('version');
		}
		return $this->doListStatusChanges(\npdc\lib\Args::get('id'), $version);
	}
	
	/**
	 * Show list of publications
	 *
	 * @return void
	 */
	public function showList(){
		$this->class = 'list publication';
		$this->title = 'Publications';
		$this->left = parent::showFilters($this->controller->formId);
		$list = $this->model->getList(isset($_SESSION[$this->controller->formId]['data']) 
				? $_SESSION[$this->controller->formId]['data'] 
				: null);
		$list2 = [];
		$list = array_values($list);
		$n = count($list);
		$page = \npdc\lib\Args::get('page') ?? 1;
		for($i = ($page-1)*\npdc\config::$rowsPerPage; $i < min($page*\npdc\config::$rowsPerPage, $n); $i++){
			$item = $list[$i];
			$item['publication'] = $this->model->getCitation($item['publication_id'], $item['publication_version'], false, false);
			$list2[] = $item;
		}
		$this->makePager($n, $page);
		$this->mid = $this->displayTable('publication', $list2
				, ['publication'=>'']
				, ['publication', 'publication_id']
				, true
				, true
			);
	}

	/**
	 * Show single publication
	 *
	 * @param intger $publication publication id
	 * @return void
	 */
	public function showItem($publication){
		if(\npdc\lib\Args::get('action') !== 'new'){
			$this->canEdit = isset($this->session->userId) 
				&& ($this->session->userLevel === NPDC_ADMIN || $this->model->isEditor($publication, $this->session->userId));
			if(\npdc\lib\Args::exists('version')){
				$this->data = $this->model->getById($publication, \npdc\lib\Args::get('version'));
				if(!$this->canEdit && !in_array($this->data['record_status'], ['published', 'archived'])){
					$this->data = false;
				}
			} else {
				$this->data = $this->model->getById($publication);
			}
			$this->version = $this->data['publication_version'];
			
			if($publication !== 'new'){
				$this->versions = $this->model->getVersions($publication);
			}

			if($this->canEdit && is_null($this->controller->display) && count($this->versions) > 1){
				$v = \npdc\lib\Args::exists('version') ? \npdc\lib\Args::get('version') : 'published';
				$_SESSION['notice'] .= 'See version <select id="versionSelect" style="width:auto">';
				foreach($this->versions as $version){
					$_SESSION['notice'] .= '<option value="'.BASE_URL.'/publication/'.$publication.'/'.$version['publication_version'].'" '
						.(in_array($v, [$version['publication_version'], $version['record_status']]) ? 'selected=true' : '')
						.'>'.$version['publication_version'].' - '.$version['record_status'].'</option>';
				}
				$_SESSION['notice'] .= '</select>';
			}

			if($this->data === false && $this->canEdit && in_array(\npdc\lib\Args::get('action'), ['edit', 'submit', 'warnings'])){
				$this->data = $this->model->getById($publication, $this->versions[0]['publication_version']);
			}
		}

		if($this->data === false && $publication !== 'new'){
			if(count($this->versions) === 0){
				$this->title = 'Not found';
				$this->mid .= 'The requested publication could not be found';
				http_response_code(404);
			} elseif(!$this->canEdit){
				if($this->session->userLevel === NPDC_PUBLIC){
					$this->title = 'Please login';
					$this->mid .= 'Please login<script type="text/javascript" language="javascript">$.ready(openOverlay(\''.BASE_URL.'/login?notice=login\'));</script>';
				} else {
					$this->title = 'No access';
					$this->mid .= 'No access';
				}
			} elseif(\npdc\lib\Args::exists('version')){
				$this->title = 'No version '.\npdc\lib\Args::get('version').' found';
				$this->mid .= 'There is no version '.\npdc\lib\Args::get('version').' of this publication.';
			}
		} elseif(in_array(NPDC_OUTPUT, ['ris', 'bib'])) {
			$this->showCitation();
		} else {
			if(\npdc\lib\Args::get('action') === 'warnings') {
				$this->title = 'Please check - '.$this->data['title'];
				$this->mid = $this->controller->showWarnings();
			} elseif(is_null($this->controller->display)){
				$changed = $this->controller->recordChanged($this->data['publication_id'], $this->data['publication_version']);
				if(!$changed){
					if($this->data['record_status'] === 'draft'){
						$_SESSION['notice'] .= ' Publishing this draft is not possible since it doesn\'t appear to be different than the published record.';
					}
				} elseif(\npdc\lib\Args::get('action') === 'submit' && $this->data['record_status'] === 'draft'){
					$_SESSION['notice'] = $this->controller->submitForm;
				} elseif($this->data['record_status'] !== 'published'){
					if($this->session->userLevel === NPDC_ADMIN && $this->data['record_status'] === 'submitted'){
						if(\npdc\lib\Args::get('action') !== 'submitted'){
							header('Location: '.BASE_URL.'/publication/'.\npdc\lib\Args::get('id').'/submitted');
							die();
						}
						$_SESSION['notice'] = $this->controller->publishForm;
					}
					$_SESSION['notice'] .= ' You are looking at a '.$this->data['record_status'].' version of this page'.($this->data['record_status'] === 'draft' ? $this->controller->draftMsg : '');
					if(!$this->canEdit){
						$cur = $this->model->getById($this->data['publication_id']);
						$_SESSION['notice'] .= ', the current can version can be found <a href="'.BASE_URL.'/publication/'.$cur['uuid'].'">here</a>';
					}
				} 
				$this->title = 'Publication - '.$this->data['title'].(is_null($this->data['acronym']) ? '' : ' ('.$this->data['acronym'].')');
				$this->class = 'detail';
				$this->mid .= parent::parseTemplate('publication_mid', $model, $this->data);
				$this->right = parent::parseTemplate('publication_right', $model, $this->data);
				$this->bottom = parent::parseTemplate('foot_technical');
				if(!\npdc\lib\Args::exists('uuid') || !\npdc\lib\Args::exists('uuidtype')){
					$this->showCanonical();
				}	
			} else {
				$this->title = (\npdc\lib\Args::get('action') === 'new') ? 'New publication' : 'Edit publication - '.$this->data['title'];
				$this->baseUrl .= '/'.$this->versions[0]['publication_version'];
				$pages = null;
				$this->loadEditPage($pages);
			}
		}
	}

	private function showCitation(){
		$url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].BASE_URL.'/publication/'.$this->data['uuid'];
		$authors = explode('; ', str_replace(' &amp;', ';', $this->model->getAuthors($this->data['publication_id'], $this->data['publication_version'], 9999, ';')));
		foreach($authors as $author){
			list($last, $first) = explode(', ', $author);
			if(empty($str)){
				$aut = $last;
			} else {
				$str .= ' and ';
			}
			$str .= str_replace('  ', ' ', str_replace('.', ' ', $first).' {'.$last.'}');
		}
		
		$id = str_replace(' ', '', $aut.substr($citation['release_date'] ?? $this->data['insert_timestamp'],0,4).substr($citation['title'] ?? $this->data['title'], 0,5));		
		include('template/publication/'.NPDC_OUTPUT.'.php');
		header('Content-type: '.$content_type);
		header("Content-Disposition: attachment; filename=\"" . $this->data['uuid'].'.'.NPDC_OUTPUT . "\""); 
		echo strip_tags($output);
		die();
	}
}
