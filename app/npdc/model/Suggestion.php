<?php

namespace npdc\model;

class Suggestion{
	protected $fpdo;
	
	public function __construct(){
		$this->fpdo = \npdc\lib\Db::getFPDO();
	}
	
	public function getList($field){
		return $this->fpdo
			->from('suggestion')
			->where('field', $field)
			->orderBy('suggestion')
			->fetchAll();
	}
}