<?php


namespace npdc\model;

class Request {
	protected $fpdo;
	
	public function __construct() {
		$this->fpdo = \npdc\lib\Db::getFPDO();
	}
	
	public function getList($person_id, $userLevel){
		$q = $this->fpdo
			->from('access_request')
			->orderBy('request_timestamp DESC');
		switch($userLevel){
			case NPDC_ADMIN:
				//no filter
				break;
			case NPDC_EDITOR:
				$q->where('person_id=:person_id OR dataset_id IN (SELECT dataset_id FROM dataset INNER JOIN dataset_person USING (dataset_id) WHERE record_status=\'published\' AND person_id=:person_id AND dataset_version<=dataset_version AND editor)', [':person_id'=>$person_id]);
				//see own requests and all request of own datasets
				break;
			case NPDC_USER:
				$q->where('person_id', $person_id);
		}
		return $q->fetchAll();
	}
	public function getById($id){
		return $this->fpdo->from('access_request', $id)->fetch();
	}
	
	public function getFiles($request_id){
		return $this->fpdo
			->from('access_request_file')
			->join('file')->select('file.*')
			->where('access_request_id', $request_id)
			->fetchAll();
	}

	public function insertRequest($data){
		return \npdc\lib\Db::insertReturnId('access_request', $data);
	}
	
	public function updateRequest($id, $data){
		$this->fpdo->update('access_request', $data, $id)->execute();
	}

	public function insertFile($data){
		$this->fpdo
			->insertInto('access_request_file', $data)
			->execute();
	}
}