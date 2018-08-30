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
	protected $args;
	protected $controller;
	protected $userLevelAdd = NPDC_EDITOR;//minimum user level required to add a new publication
	public $canEdit = false;
	public $baseUrl;
	public $versions;

	/**
	 * Constructor
	 *
	 * @param object $session login information
	 * @param array $args url parameters
	 * @param object $controller account controller
	 */
	public function __construct($session, $args, $controller){
		$this->session = $session;
		$this->args = $args;
		$this->controller = $controller;
		$this->baseUrl = implode('/', array_slice($args, 0, 2));
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
		if(count($this->args) > 2){
			if(in_array($this->args[2], ['edit', 'submit'])){
				$version = $this->versions[0]['publication_version'];
			} elseif(is_numeric($this->args[2])){
				$version = $this->args[2];
			} else {
				foreach($this->versions as $version){
					if($version['record_status'] === $this->args[2]){
						$version = $version['publication_version'];
						break;
					}
				}
			}
		}
		return $this->doListStatusChanges($this->args[1], $version);
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
				: null
			);
		$list2 = [];
		foreach($list as $item){
			$item['authors'] = $this->model->getAuthors($item['publication_id'], $item['publication_version']);
			$list2[] = $item;
		}
		$this->mid = $this->displayTable('publication', $list2
				, ['authors'=>'Authors',
					'title'=>'Title', 
					'year'=>'Year', 
					'journal'=>'Source']
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
		$this->canEdit = isset($this->session->userId) 
			&& ($this->session->userLevel === NPDC_ADMIN || $this->model->isEditor($publication, $this->session->userId));
		if(count($this->args) > 2 && $this->args[2] !== 'edit'){
			$this->data = $this->model->getById($publication, $this->args[2]);
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
			$v = count($this->args) < 3 ? 'published' : $this->args[2];
			$_SESSION['notice'] .= 'See version <select id="versionSelect" style="width:auto">';
			foreach($this->versions as $version){
				$_SESSION['notice'] .= '<option value="'.BASE_URL.'/publication/'.$publication.'/'.$version['publication_version'].'" '
					.(in_array($v, [$version['publication_version'], $version['record_status']]) ? 'selected=true' : '')
					.'>'.$version['publication_version'].' - '.$version['record_status'].'</option>';
			}
			$_SESSION['notice'] .= '</select>';
		}

		if($this->data === false && $this->canEdit 
			&& (count($this->args) < 3 || in_array($this->args[2], ['edit', 'submit', 'warnings']))){
			$this->data = $this->model->getById($publication, $this->versions[0]['publication_version']);
		}

		if($this->data === false && $publication !== 'new'){
			if(count($this->versions) === 0){
				$this->title = 'Not found';
				$this->mid .= 'The requested publication could not be found';
			} elseif(!$this->canEdit){
				if($this->session->userLevel === NPDC_PUBLIC){
					$this->title = 'Please login';
					$this->mid .= 'Please login<script type="text/javascript" language="javascript">$.ready(openOverlay(\''.BASE_URL.'/login?notice=login\'));</script>';
				} else {
					$this->title = 'No access';
					$this->mid .= 'No access';
				}
			} elseif(is_numeric($this->args[2])) {
				$this->title = 'No version '.$this->args[2].' found';
				$this->mid .= 'There is no version '.$this->args[2].' of this publication.';
			} else {
				$this->title = 'No '.$this->args[2].' version found';
				$this->mid .= 'There is no '.$this->args[2].' version of this publication.';
			}
		} else {
			if($this->args[2] === 'warnings') {
				$this->title = 'Please check - '.$this->data['title'];
				$this->mid = $this->controller->showWarnings();
			} elseif(is_null($this->controller->display)){
				$changed = $this->controller->recordChanged($this->data['publication_id'], $this->data['publication_version']);
				if(!$changed){
					if($this->data['record_status'] === 'draft'){
						$_SESSION['notice'] .= ' Publishing this draft is not possible since it doesn\'t appear to be different than the published record.';
					}
				} elseif($this->args[2] === 'submit' && $this->data['record_status'] === 'draft'){
					$_SESSION['notice'] = $this->controller->submitForm;
				} elseif($this->data['record_status'] !== 'published'){
					if($this->session->userLevel === NPDC_ADMIN && $this->data['record_status'] === 'submitted'){
						if($this->args[2] !== 'submitted'){
							header('Location: '.BASE_URL.'/publication/'.$this->args[1].'/submitted');
							die();
						}
						$_SESSION['notice'] = $this->controller->publishForm;
					}
					$_SESSION['notice'] .= ' You are looking at a '.$this->data['record_status'].' version of this page'.($this->data['record_status'] === 'draft' ? $this->controller->draftMsg : '');
					if(!$this->canEdit){
						$cur = $this->model->getById($this->data['publication_id']);
						$_SESSION['notice'] .= ', the current can version can be found <a href="'.BASE_URL.'/'.$cur['uuid'].'">here</a>';
					}
				} 
				$this->title = 'Publication - '.$this->data['title'].(is_null($this->data['acronym']) ? '' : ' ('.$this->data['acronym'].')');
				$this->class = 'detail';
				$this->mid .= parent::parseTemplate('publication_mid', $model, $this->data);
				$this->right = parent::parseTemplate('publication_right', $model, $this->data);
				if(!defined('NPDC_UUID')){
					$this->showCanonical();
				}	
			} else {
				$this->title = ($publication === 'new') ? 'New publication' : 'Edit publication - '.$this->data['title'];
				$this->baseUrl .= '/'.$this->versions[0]['publication_version'];
				$pages = null;
				$this->loadEditPage($pages);
			}
		}
	}
}
