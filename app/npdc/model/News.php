<?php

namespace npdc\model;

class News {
	protected $fpdo;
	
	public function __construct() {
		$this->fpdo = \npdc\lib\Db::getFPDO();
	}
	
	public function getList(){
		return $this->fpdo
			->form('news')
			->fetchAll();
	}
	
	public function getById($id){
		return $this->fpdo
			->from('news', $id)
			->fetch();
	}
	
	public function getLatest($n = 1){
		return $this->fpdo
			->from('news')
			->where('published < CURRENT_TIMESTAMP')
			->where('(show_till IS NULL OR show_till > CURRENT_TIMESTAMP)')
			->orderBy('published DESC')
			->limit($n)
			->fetchAll();
	}
	
}