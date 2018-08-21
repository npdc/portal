<?php

/**
 * Country model
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\model;

class Country {
	protected $fpdo;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->fpdo = \npdc\lib\Db::getFPDO();
	}
	
	/**
	 * List countries grouped by continent
	 *
	 * @return array list of countries with continent name
	 */
	public function getListByContinent(){
		return $this->fpdo
			->from('country')
			->join('continent')->select('continent_name')
			->orderBy('continent_id, country_id')
			->fetchAll();
	}
}