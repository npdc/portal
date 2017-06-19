<?php

/**
 * login class
 */

namespace npdc\lib;

class Login {
	public $userLevel = 0;
	public $name;
	public $levels = [];
	public $levelDetails = [];
	private $model;
	private $user;
	
	public function __construct(){
		$this->model = new \npdc\model\Person();
		
		foreach($this->model->getUserLevels() as $level){
			define('NPDC_'.strtoupper($level['label']), (int)$level['user_level_id']);
			$this->levels[$level['user_level_id']] = $level['label'];
			$this->levelDetails[$level['user_level_id']] = ['name'=>$level['name'], 'description'=>$level['description']];
		}

		if(array_key_exists('logout', $_GET)){
			if(array_key_exists('user', $_SESSION)){
				session_unset();
				$_SESSION['notice'] = 'You are now logged out.';
			}
			header('Location: '.(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : BASE_URL));
			die();
		}
		if(array_key_exists('user', $_SESSION)){
			$this->model = new \npdc\model\Person();
			$this->user = $this->model->getById($_SESSION['user']['id']);
			if(empty($this->user)){
				session_unset();
				$_SESSION['notice'] = 'Your account has been removed';
			} else {
				$this->userLevel = array_search($this->user['user_level'], $this->levels);
				$this->name = $this->user['name'];
				$this->organization_id = $this->user['organization_id'];
				$this->userId = $_SESSION['user']['id'];
			}
		}
	}
}