<?php

/**
 * Data access request model
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */


namespace npdc\model;

class Request {
	private $dsql;

	/**
	 * Constructor
	 */
	public function __construct(){
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
		$q = $this->dsql->dsql()
			->table('access_request')
			->order('request_timestamp DESC');
		switch($userLevel){
			case NPDC_ADMIN:
				//no filter
				break;
			case NPDC_EDITOR:
				$q->where($q->orExpr()
					->where('person_id', $person_id)
					->where('dataset_id', 'IN', $q->dsql()
						->table('dataset')->field('dataset.dataset_id')
						->where('record_status', 'published')
						->join('dataset_person.dataset_id', 'dataset_id', 'inner')
						->where(\npdc\lib\Db::joinVersion('dataset', 'dataset_person',false))
						->where('editor')
						->where('person_id', $person_id)
				));
				//see own requests and all request of own datasets
				break;
			case NPDC_USER:
				$q->where('person_id', $person_id);
		}
		return $q->get();
	}

	/**
	 * Get request details by id
	 *
	 * @param integer $id request id
	 * @return array request details
	 */
	public function getById($id){
		return \npdc\lib\Db::get('access_request', $id);
	}
	
	/**
	 * Get list of files of request
	 *
	 * @param integer $request_id request id
	 * @return array list of files
	 */
	public function getFiles($request_id){
		return $this->dsql->dsql()
			->table('access_request_file')
			->join('file.file_id', 'file_id', 'inner')
			->where('access_request_id', $request_id)
			->get();
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
		return \npdc\lib\Db::insert('access_request', $data, true);
	}
	
	/**
	 * Update request details
	 *
	 * @param integer $id request id
	 * @param array $data new data
	 * @return void
	 */
	public function updateRequest($id, $data){
		\npdc\lib\Db::update('access_request', $id, $data);
	}

	/**
	 * Add file to request
	 *
	 * @param array $data record to insert
	 * @return void
	 */
	public function insertFile($data){
		\npdc\lib\Db::insert('access_request_file', $data);
	}
}