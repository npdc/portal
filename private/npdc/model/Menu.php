<?php

/**
 * menu model
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\model;

class Menu {
	protected $fpdo;
	
	/**
	 * Constructor
	 */
	public function __construct(){
		$this->fpdo = \npdc\lib\Db::getFPDO();
	}
	
	/**
	 * Get menu items of (sub)menu
	 *
	 * @param integer $parent id of parent item, nullable
	 * @param string $userLevel user level of current user
	 * @return void
	 */
	public function getItems($parent, $userLevel){
		return $this->fpdo
			->from('menu')
			->where('min_user_level', $userLevel)
			->where('parent_menu_id', $parent)
			->orderBy('sort')
			->fetchAll();
	}
}