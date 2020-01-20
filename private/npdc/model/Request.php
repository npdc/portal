<?php

/**
 * Data access request model
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */


namespace npdc\model;

class Request {
	private $fpdo;
	private $dsql;

	/**
	 * Constructor
	 */
	public function __construct(){
		$this->fpdo = \npdc\lib\Db::getFPDO();
		$this->dsql = \npdc\lib\Db::getDSQLcon();
	}
	
	/**
	 * GETTERS
	 */

	/**
	 * Get list of requests
	 * 
	 * Depending on user level only own requests are shown, also request the user has to decide on or all request are shown
	 *
	 * @param integer $person_id current user id
	 * @param integer $userLevel user level of current user
	 * @return array list of requests
	 */
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

	/**
	 * Get request details by id
	 *
	 * @param integer $id request id
	 * @return array request details
	 */
	public function getById($id){
		return $this->fpdo->from('access_request', $id)->fetch();
	}
	
	/**
	 * Get list of files of request
	 *
	 * @param integer $request_id request id
	 * @return array list of files
	 */
	public function getFiles($request_id){
		return $this->fpdo
			->from('access_request_file')
			->join('file')->select('file.*')
			->where('access_request_id', $request_id)
			->fetchAll();
	}

	/**
	 * SETTERS
	 */

	 /**
	  * Insert new request
	  *
	  * @param array $data request details
	  * @return integer id of new request
	  */
	public function insertRequest($data){
		return \npdc\lib\Db::insertReturnId('access_request', $data);
	}
	
	/**
	 * Update request details
	 *
	 * @param integer $id request id
	 * @param array $data new data
	 * @return void
	 */
	public function updateRequest($id, $data){
		$this->fpdo->update('access_request', $data, $id)->execute();
	}

	/**
	 * Add file to request
	 *
	 * @param array $data record to insert
	 * @return void
	 */
	public function insertFile($data){
		$this->fpdo
			->insertInto('access_request_file', $data)
			->execute();
	}
}