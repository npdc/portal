<?php

namespace npdc\lib;

class Menu{
	private $model;
	private $userLevel;
	private $current;
	private $session;
	
	private function __construct($session, $current){
		$this->model = new \npdc\model\Menu();
		$this->current = $current;
		$this->userLevel = array_slice($session->levels, 0, $session->userLevel+1);
		$this->session = $session;
	}
	
	public function generate($parent = null){
		$active = false;
		$return = '<ul>';
		foreach($this->model->getItems($parent, $this->userLevel) as $item){
			if(!(\npdc\config::$partEnabled[$item['url']] ?? true) && $this->session->userLevel < NPDC_ADMIN){
				continue;
			}
			if(is_null($item['url'])){
				$res = $this->generate($item['menu_id']);
				$return .= '<li class="sub'.($res[1] ? ' active-child' : '').'"><span>'.$item['label'].'</span>'.$res[0].'</li>';
			} else {
				$active = $this->current === $item['url'] || $active;
				$return .= '<li'.($this->current === $item['url'] ? ' class="active"' : '').'><a href="'.BASE_URL.'/'.$item['url'].'">'.$item['label'].'</a></li>';
			}
		}
		$return .= '</ul>';
		return [$return,$active];
	}
	public static function getMenu($session, $current){
		$instance = new \npdc\lib\Menu($session, $current);
		return $instance->generate()[0];
	}
}