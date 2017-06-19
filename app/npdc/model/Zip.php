<?php


namespace npdc\model;

class Zip {
	protected $fpdo;
	
	public function __construct() {
		$this->fpdo = \npdc\lib\Db::getFPDO();
	}
	
	public function getById($id){
		return $this->fpdo->from('zip', $id)->fetch();
	}

	public function insertZip($data){
		return \npdc\lib\Db::insertReturnId('zip', $data);
	}
	
	public function updateZip($id, $data){
		$this->fpdo->update('zip', $data, $id)->execute();
	}
	
	public function insertFile($data){
		$this->fpdo
			->insertInto('zip_files', $data)
			->execute();
	}
}