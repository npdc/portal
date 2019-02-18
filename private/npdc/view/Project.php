<?php

/**
 * the view for projects
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\view;

class Project extends Base{
	public $title;
	public $left;
	public $mid;
	public $right;
	public $class;
	protected $session;
	protected $args;
	protected $controller;
	protected $userLevelAdd = NPDC_ADMIN;//minimum user level required to add a new project
	public $canEdit = false;
	public $baseUrl;
	public $versions;
	
	/**
	 * Constructor
	 *
	 * @param object $session login information
	 * @param array $args url parameters
	 * @param object $controller project controller
	 */
	public function __construct($session, $args, $controller){
		$this->session = $session;
		$this->args = $args;
		$this->controller = $controller;
		$this->baseUrl = implode('/', array_slice($args, 0, 2));
		$this->model = new \npdc\model\Project();
		parent::__construct();
	}
	
	/**
	 * List status changes of record
	 *
	 * @return void
	 */
	public function listStatusChanges(){
		$version = $this->version;
		if(array_key_exists('action', $this->args) && in_array($this->args['action'], ['edit', 'submit'])){
			$version = $this->versions[0]['project_version']; 
		} elseif (array_key_exists('version', $this->args)){
			$version = $this->args['version'];
		}
		return $this->doListStatusChanges($this->args['id'], $version);
	}
	
	/**
	 * display list of projects
	 * 
	 * @return void
	 */
	public function showList(){
		$this->class = 'list project';
		$this->title = 'Projects';
		$this->left = parent::showFilters('projectlist');
		$list = $this->model->getList(isset($_SESSION[$this->controller->formId]['data'])
				? $_SESSION[$this->controller->formId]['data'] 
				: null
			);
		foreach($list as $i=>&$item){
			$add = '';
			$parent = $this->model->getParents($item['project_id']);
			if(count($parent) > 0){
				$add .= '<div class="parent">Subproject of \''.$parent[0]['title'].'\'</div>';
			}
			$children = $this->model->getChildren($item['project_id']);
			if(count($children) > 0){
				foreach($children as $child){
					$add .= '<div class="child">'.$child['title'].'</div>';
				}
			}
			if(!empty($add)){
				$item['title'] .= '<div class="related"></div>'.$add;
			}
		}
		$this->mid = '<div class="related"><i>Projects with related projects</i> <a href="javascript:toggleRelated(false)">Show related projects</a></div><div class="noRelated"><a href="javascript:toggleRelated(true)">Hide related projects</a></div>'
			.$this->displayTable('project', $list
				, ['title'=>'Title',
					'nwo_project_id'=>'Funding id',
					'date_start'=>'Start date',
					'date_end'=>'End date']
				, ['project', 'project_id']
				, true
				, true
			);
	}
	
	/**
	 * display single project
	 * 
	 * @param string $project project id
	 * @return void
	 */
	public function showItem($project){
		$this->canEdit = isset($this->session->userId) 
			&& ($this->session->userLevel === NPDC_ADMIN || $this->model->isEditor($project, $this->session->userId));
		if(array_key_exists('version', $this->args)){
			$this->data = $this->model->getById($project, $this->args['version']);
			if(!$this->canEdit && !in_array($this->data['record_status'], ['published', 'archived'])){
				$this->data = false;
			}
		} else {
			$this->data = $this->model->getById($project);
		}
		$this->version = $this->data['project_version'];
		
		if($project !== 'new'){
			$this->versions = $this->model->getVersions($project);
		}
		if($this->canEdit && is_null($this->controller->display) && count($this->versions) > 1){
			$v = array_key_exists('version', $this->args) ? $this->args['version'] : 'published';
			$_SESSION['notice'] = 'See version <select id="versionSelect" style="width:auto">';
			foreach($this->versions as $version){
				$_SESSION['notice'] .= '<option value="'.BASE_URL.'/project/'.$project.'/'.$version['project_version'].'" '
					.(in_array($v, [$version['project_version'], $version['record_status']]) ? 'selected=true' : '')
					.'>'.$version['project_version'].' - '.$version['record_status'].'</option>';
			}
			$_SESSION['notice'] .= '</select>';
		}
		
		if($this->data === false && $this->canEdit && in_array($this->args['action'], ['edit', 'submit', 'warnings'])){
			$this->data = $this->model->getById($project, $this->versions[0]['project_version']);
		}
		
		if($this->data === false && $project !== 'new'){
			if(count($this->versions) === 0){
				$this->title = 'Not found';
				$this->mid .= 'The requested project could not be found';
			} elseif(!$this->canEdit){
				if($this->session->userLevel === NPDC_PUBLIC){
					$this->title = 'Please login';
					$this->mid .= 'Please login<script type="text/javascript" language="javascript">$.ready(openOverlay(\''.BASE_URL.'/login?notice=login\'));</script>';
				} else {
					$this->title = 'No access';
					$this->mid .= 'No access';
				}
			} elseif(array_key_exists('version', $this->args)) {
				$this->title = 'No version '.$this->args['version'].' found';
				$this->mid .= 'There is no version '.$this->args['version'].' of this project.';
			}
		} else {
			if(is_null($this->controller->display)){
				$changed = $this->controller->recordChanged($this->data['project_id'], $this->data['project_version']);
				if(!$changed){
					if($this->data['record_status'] === 'draft'){
						$_SESSION['notice'] .= ' Publishing this draft is not possible since it doesn\'t appear to be different than the published record.';
					}
				} elseif($this->args['action'] === 'submit' && $this->data['record_status'] === 'draft'){
					$_SESSION['notice'] = $this->controller->submitForm;
				} elseif($this->data['record_status'] !== 'published'){
					if($this->session->userLevel === NPDC_ADMIN && $this->data['record_status'] === 'submitted'){
						if($this->args['action'] !== 'submitted'){
							header('Location: '.BASE_URL.'/project/'.$this->args['id'].'/submitted');
							die();
						}
						$_SESSION['notice'] = $this->controller->publishForm;
					}
					$_SESSION['notice'] .= ' You are looking at a '.$this->data['record_status'].' version of this page'.($this->data['record_status'] === 'draft' ? $this->controller->draftMsg : '');
					if(!$this->canEdit){
						$cur = $this->model->getById($this->data['project_id']);
						$_SESSION['notice'] .= ', the current can version can be found <a href="'.BASE_URL.'/project/'.$cur['uuid'].'">here</a>';
					}
				} 
				$this->title = 'Project - '.$this->data['title'].(is_null($this->data['acronym']) ? '' : ' ('.$this->data['acronym'].')');
				$this->class = 'detail';
				$this->mid .= parent::parseTemplate('project_mid');
				$this->right = parent::parseTemplate('project_right');
				if(!defined('NPDC_UUID')){
					$this->showCanonical();
				}	
			} elseif($this->args['action'] === 'warnings') {
				$this->title = 'Please check - '.$this->data['title'];
				$this->mid = $this->controller->showWarnings();
			} else {
				$this->title = ($project === 'new') ? 'New project' : 'Edit project - '.$this->data['title'];
				$this->baseUrl .= '/'.$this->versions[0]['project_version'];
				$pages = null;
				$this->loadEditPage($pages);
			}
		}
	}
}
