<?php

/**
 * Funding program controller
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\model;

class Program {
	protected $fpdo;
	
	/**
	 * Constructor
	 */
	public function __construct(){
		$this->fpdo = \npdc\lib\Db::getFPDO();
	}
	
	/**
	 * List programs
	 *
	 * @param string $filter (optional) only show when present in specified content type
	 * @return array list of programs
	 */
	public function getList($filter = null){
		$q = $this->fpdo
			->from('program')
			->orderBy('sort');
		if(!is_null($filter)){
			$q2 = null;
			switch($filter){
				case 'project':
					$q2 = $this->fpdo
						->from('project a');
					break;
				case 'publication':
					$q2 = $this->fpdo
						->from('project_publication')
						->join('publication a')
						->join('project b')
						->where('b.record_status', 'published');
					
					break;
			}
			if(!is_null($q2)){
				$q2->select('DISTINCT(program_id)')
					->where('a.record_status', 'published');
				$res = $q2->fetchAll('program_id', 'program_id');
				if(count($res) > 0){
					$q->where('program_id', array_keys($res));
				} else {
					return null;
				}
			}
		}
		return $q->fetchAll();
	}
	
	/**
	 * Get program details
	 *
	 * @param integer $id program id
	 * @return array program details
	 */
	public function getById($id){
		return $this->fpdo
			->from('program', $id)
			->fetch();
	}

	/**
	 * Add new program
	 *
	 * @param array $data Program details to insert
	 * @return boolean insert succesfull
	 */
	public function insertProgram($data){
		return $this->fpdo
			->insertInto('program')
			->values($data)
			->execute();
	}
	
	/**
	 * Update program
	 *
	 * @param array $data new details
	 * @param integer $id program id
	 * @return boolean update successfull
	 */
	public function updateProgram($data, $id){
		return $this->fpdo
			->update('program', $data, $id)
			->execute();
	}
}