<?php

/**
 * File model, used for data file
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\model;

class File {
	protected $fpdo;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->fpdo = \npdc\lib\Db::getFPDO();
	}
	
	/**
	 * Add file details to database
	 *
	 * @param array $data file data
	 * @return integer id of newly inserted record
	 */
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
	
	/**
	 * Update file details
	 *
	 * @param integer $id file id
	 * @param array $data new details
	 * @return void
	 */
	public function updateFile($id, $data){
		$this->fpdo->update('file', $data, $id)->execute();
	}
	
	/**
	 * delete files that are left as draft
	 *
	 * @param string $form_id id of form used to add file, is in form <type>/<id>
	 * @return void
	 */
	public function cancelDrafts($form_id){
		$this->fpdo
			->update('file')
			->set('record_state', 'cancelled')
			->where('record_state', 'draft')
			->where('form_id', $form_id)
			->execute();
	}
	
	/**
	 * Get file details by id
	 *
	 * @param integer $id file id
	 * @return array file details
	 */
	public function getFile($id){
		return $this->fpdo
			->from('file', $id)
			->fetch();
	}
	
	/**
	 * Get draft records based on form id
	 *
	 * @param string $form_id id of form
	 * @return array list of files
	 */
	public function getDrafts($form_id){
		return $this->fpdo
		->from('file')
		->where('record_state', 'draft')
		->where('form_id', $form_id)
		->fetchAll();
	}

	/**
	 * Get number of times a file has been downloaded
	 *
	 * @param integer $file_id file id
	 * @return integer download count
	 */
	public function getDownloadCount($file_id){
		return count($this->fpdo
			->from('zip_files')
			->where('file_id', $file_id)
			->fetchAll());
	}
}