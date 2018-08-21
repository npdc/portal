<?php

/**
 * Suggestion model
 * 
 * Provides suggested values for several fields
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\model;

class Suggestion{
	protected $fpdo;
	
	/**
	 * Constructor
	 */
	public function __construct(){
		$this->fpdo = \npdc\lib\Db::getFPDO();
	}
	
	/**
	 * GETTERS
	 */

	/**
	 * Get list of suggestions for field
	 *
	 * @param string $field name of field for which to retreive suggestions
	 * @return array list of suggestions
	 */
	public function getList($field){
		return $this->fpdo
			->from('suggestion')
			->where('field', $field)
			->orderBy('suggestion')
			->fetchAll();
	}

	/**
	 * SETTERS
	 * 
	 * Not present, has to be updated directly in the database currently
	 */
}