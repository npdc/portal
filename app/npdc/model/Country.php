<?php

namespace npdc\model;

class Country {
	protected $fpdo;
	
	public function __construct() {
		$this->fpdo = \npdc\lib\Db::getFPDO();
	}
	
	public function getListByContinent(){
		return $this->fpdo
			->from('country')
			->join('continent')->select('continent_name')
			->orderBy('continent_id, country_id')
			->fetchAll();
	}
}