<?php

/**
 * menu model
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\model;

class Menu {
	private $dsql;
	
	/**
	 * Constructor
	 */
	public function __construct(){
		$this->dsql = \npdc\lib\Db::getDSQLcon();
	}
	
	/**
	 * Get menu items of (sub)menu
	 *
	 * @param integer $parent id of parent item, nullable
	 * @param string $userLevel user level of current user
	 * @return void
	 */
	public function getItems($parent, $userLevel){
		$m = $this->dsql->dsql()->table('menu')
			->where('min_user_level', $userLevel);
		if(!is_null($parent)){
			$m->where('parent_menu_id', $parent);
		} else {
			$m->where('parent_menu_id IS NULL');
		}
		return $m->order('sort')
			->get();
	}
}