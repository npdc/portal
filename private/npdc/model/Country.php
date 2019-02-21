<?php

/**
 * Country model
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\model;

class Country {
	protected $dsql;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->dsql = \npdc\lib\Db::getDSQLcon();
	}
	
	/**
	 * List countries grouped by continent
	 *
	 * @return array list of countries with continent name
	 */
	public function getListByContinent(){
		return $this->dsql->dsql()->table('country')
			->join('continent.continentid', 'continent_id')
			->order('country.continent_id, country_name')
			->get();
	}

	public function getList(){
		return $this->dsql->dsql()->table('country')
			->order('country_name')
			->get();
	}
}