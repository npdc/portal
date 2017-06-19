<?php

namespace npdc\model;

class File {
	protected $fpdo;
	
	public function __construct() {
		$this->fpdo = \npdc\lib\Db::getFPDO();
	}
	
	public function insertFile($data){
		$this->fpdo
			->insertInto('file')
			->values($data)
			->execute();
		return $this->fpdo
			->from('file')
			->where($data)
			->fetch()['file_id'];
	}
	
	public function updateFile($id, $data){
		$this->fpdo->update('file', $data, $id)->execute();
	}
	
	public function cancelDrafts($form_id){
		$this->fpdo
			->update('file')
			->set('record_state', 'cancelled')
			->where('record_state', 'draft')
			->where('form_id', $form_id)
			->execute();
	}
	
	public function getFile($id){
		return $this->fpdo
			->from('file', $id)
			->fetch();
	}
	
	public function getDrafts($form_id){
		return $this->fpdo->from('file')->where('record_state', 'draft')->where('form_id', $form_id)->fetchAll();
	}
}