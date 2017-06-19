<?php

namespace npdc\model;

class Organization {
	protected $fpdo;
	
	public function __construct(){
		$this->fpdo = \npdc\lib\Db::getFPDO();
	}
	
	public function getList($filter = null){
		$q = $this->fpdo
			->from('organization')
			->orderBy('organization_name');
		if(!is_null($filter)){
			$q2 = null;
			switch ($filter){
				case 'dataset':
					$q2 = $this->fpdo
						->from('dataset_person')
						->join('dataset')
						->where('record_status', 'published');
					break;
				case 'project':
					$q2 = $this->fpdo
						->from('project_person')
						->join('project')
						->where('record_status', 'published');
					break;
				case 'publication':
					$q2 = $this->fpdo
						->from('publication_person')
						->join('publication')
						->where('record_status', 'published');
					break;
			}
			$q->where('country_id', 'NL');
			if(!is_null($q2)){
				$q2->select('DISTINCT (organization_id)')
					->where('organization_id IS NOT NULL')
					->where('record_status', 'published');
				$res = $q2->fetchAll('organization_id', 'organization_id');
				if(count($res) > 0){
					$q->where('organization_id', array_keys($res));
				} else {
					return null;
				}
			}
		}
		return $q->fetchAll();
	}
	
	public function search($string, $exclude = null){
		$q = $this->fpdo
			->from('organization')
			->orderBy('organization_name');
		if(!empty($string)){
			$q->where('(organization_name REGEXP :search1 OR dif_code REGEXP :search2 OR dif_name REGEXP :search3)', [':search1'=>$string, ':search2'=>$string, ':search3'=>$string]);
		}
		if(is_array($exclude) && count($exclude) > 0){
			$q->where('organization_id NOT', $exclude);
		}
		return $q->fetchAll();
	}
	
	public function getById($id){
		return $this->fpdo
			->from('organization', $id)
			->leftJoin('country')->select('country_name')
			->fetch();
	}

	public function insertOrganization($data){
		$this->fpdo
			->insertInto('organization')
			->values($data)
			->execute();
		return $this->fpdo
			->from('organization')
			->where($data)
			->orderBy('organization_id')
			->fetch()['organization_id'];
	}
	
	public function updateOrganization($data, $id){
		return $this->fpdo
			->update('organization', $data, $id)
			->execute();
	}
}