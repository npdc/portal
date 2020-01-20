<?php

/**
 * Zip model
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\model;

class Zip {
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
	 * Get zip details by id
	 *
	 * @param integer $id zip id
	 * @return array zip details
	 */
	public function getById($id){
		return $this->fpdo->from('zip', $id)->fetch();
	}

	/**
	 * Get zip details by filename
	 *
	 * @param string $name filename
	 * @return array zip details
	 */
	public function getByName($name){
		return $this->fpdo->from('zip')->where('filename', $name)->fetch();
	}

	/**
	 * SETTERS
	 */

	/**
	 * Add new zip
	 *
	 * @param array $data zip details
	 * @return integer id of newly inserted zip
	 */
	public function insertZip($data){
		return \npdc\lib\Db::insertReturnId('zip', $data);
	}
	
	/**
	 * Update zip details
	 *
	 * @param integer $id zip id
	 * @param array $data new zip details
	 * @return voi
	 */
	public function updateZip($id, $data){
		$this->fpdo->update('zip', $data, $id)->execute();
	}
	
	/**
	 * Add file to zip
	 *
	 * @param array $data new record
	 * @return void
	 */
	public function insertFile($data){
		$this->fpdo
			->insertInto('zip_files', $data)
			->execute();
	}

}