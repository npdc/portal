<?php

namespace npdc\model;

class Menu {
	protected $fpdo;
	
	/**
	 * set db instance
	 */
	public function __construct(){
		$this->fpdo = \npdc\lib\Db::getFPDO();
	}
	
	public function getItems($parent, $userLevel){
		return $this->fpdo
			->from('menu')
			->where('min_user_level', $userLevel)
			->where('parent_menu_id', $parent)
			->orderBy('sort')
			->fetchAll();
	}
}