<?php

namespace npdc\model;

class Program {
	protected $fpdo;
	
	public function __construct(){
		$this->fpdo = \npdc\lib\Db::getFPDO();
	}
	
	public function getList($filter = null){
		$q = $this->fpdo
			->from('program')
			->orderBy('program_start');
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
	
	public function getById($id){
		return $this->fpdo
			->from('program', $id)
			->fetch();
	}

	public function insertProgram($data){
		return $this->fpdo
			->insertInto('program')
			->values($data)
			->execute();
	}
	
	public function updateProgram($data, $id){
		return $this->fpdo
			->update('program', $data, $id)
			->execute();
	}
}