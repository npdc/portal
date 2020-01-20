<?php

/**
 * Funding npp_theme controller
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\model;

class Npp_theme {
	private $dsql;
	
	/**
	 * Constructor
	 */
	public function __construct(){
		$this->dsql = \npdc\lib\Db::getDSQLcon();
	}
	
	/**
	 * List npp_themes
	 *
	 * @return array list of npp_themes
	 */
	public function getList(){
		return $this->dsql->dsql()
			->table('npp_theme')
			->order('npp_theme_id')
			->get();
	}

	/**
	 * Get theme by id
	 *
	 * @param int $id theme id
	 * @return array theme
	 */
	public function getById($id){
		return $this->dsql->dsql()
			->table('npp_theme')
			->where('npp_theme_id', $id)
			->get()[0];
	}
}