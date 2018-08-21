<?php

/**
 * Overlay view
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\view;

class Overlay{
	public $template = 'plain';
	public $bodyClass = 'overlay';
	public $mid;
	public $title;

	/**
	 * Constructor
	 *
	 * @param object $session login information
	 * @param array $args url parameters
	 */
	public function __construct($session, $args){
		$this->session = $session;
		$this->args = $args;
		if($args[1] === 'editor' && $session->userLevel < NPDC_EDITOR){
			header('Location: '.BASE_URL.'/login?view=overlay&referer='.$_SERVER['REQUEST_URI']);die();
		}
	}
	
	/**
	 * Unused, present for compatibility
	 *
	 * @return void
	 */
	public function showList(){
		
	}
	
	/**
	 * Display eiher editor tools or a page
	 *
	 * @return void
	 */
	public function showItem(){
		if($this->args[1] === 'editor'){
			$this->editorTools();
		} else {
			$model = new \npdc\model\Page();
			$page = $model->getByUrl($this->args[1]);
			$this->title = $page['title'];
			$this->mid = ($page['show_last_revision'] ? '<p class="last-rev">Last revision: '.date('d F Y', strtotime($page['last_update'])).'</p>' : '').$page['content'];
			if($this->args[2] === 'accept'){
				$this->mid .= '<button onclick="window.parent.closeOverlay();" style="float:right">Close '.$page['title'].'</button>';
			}
		}
	}

	/**
	 * Display editor tools
	 *
	 * @return void
	 */
	private function editorTools(){
		$this->title = ucfirst($this->args[1]);
		$editUrl = null;
		
		$url = parse_url($_GET['u']);
		$path = trim(str_replace(BASE_URL, '', $url['path']), '/');
		$parts = strlen($path) > 0 ? explode('/', $path) : [front];
		if($parts[1] !== 'new' && !in_array(ucfirst($parts[0]), ['Base', 'Form', 'Contact', 'Front'])){
			$modelClass = 'npdc\\model\\'.ucfirst($parts[0]);
			$n = 2;
			if(!file_exists(get_class_file($modelClass))){
				$modelClass = 'npdc\\model\\Page';
				$getFunction = 'url';
				$parts[1] = $parts[0];
				$n = 1;
			} elseif($parts[0] === 'page') {
				$getFunction = 'url';
			} else {
				$getFunction = 'id';
			}
			$model = new $modelClass();
			switch($getFunction){
				case 'url':
					$page = $model->getByUrl($parts[1]);
					break;
				case 'id':
					if(method_exists($model, 'getVersions')){
						$page = $model->getById($parts[1], $model->getVersions($parts[1])[0][$parts[0].'_version']);
					} else {
						$page = $model->getById($parts[1]);
					}
					break;
			}
			$pageTitle = $page['title'];
			if(!is_null($pageTitle) && (
				$this->session->userLevel === NPDC_ADMIN || 
					($modelClass !== 'npdc\\model\\Page' && $model->isEditor($parts[1], $this->session->userId))
				)){
				$editUrl = BASE_URL.'/'.implode('/', array_slice($parts, 0, $n)).'/edit';
			}
		}
		ob_start();
		include 'template/overlay_'.$this->args[1].'.php';
		$this->mid = ob_get_clean();
		ob_end_clean();
	}
}