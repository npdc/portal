<?php

/**
 * retrieve pages from the db
 */

namespace npdc\model;

class Page{
	private $fpdo;

	public function __construct(){
		$this->fpdo = \npdc\lib\Db::getFPDO();
	}
	
	/**
	 * get a page by its url
	 * @param string $url
	 * @return object
	 */
	public function getByUrl($url){
		return $this->fpdo
			->from('page')
			->where('url', $url)
			->fetch();
	}
	
	/**
	 * get persons of page
	 * @param integer $id
	 * @return array
	 */
	public function getPersons($id){
		return $this->fpdo
			->from('page_person')
			->join('person USING (person_id)')->select('person.*')
			->join('organization USING(organization_id)')->select('organization.*')
			->where('page_id', $id)
			->orderBy('sort')
			->fetchAll();
	}
	
	/**
	 * get urls of page
	 * @param intger $id
	 * @return array
	 */
	public function getUrls($id){
		return $this->fpdo
			->from('page_link')
			->where('page_id', $id)
			->orderBy('sort')
			->fetchAll();
	}
	
	public function updatePage($id, $values){
		$values['last_update'] = date('Y-m-d h:i:s');
		return $this->fpdo
			->update('page', $values, $id)
			->execute();
	}
	
	public function insertPerson($data){
		return $this->fpdo
			->insertInto('page_person')
			->values($data)
			->execute();
	}
	
	public function updatePerson($record, $data){
		return $this->fpdo
			->update('page_person')
			->set($data)
			->where($record)
			->execute();
	}
	
	public function deletePerson($page, $persons){
		$q = $this->fpdo
			->deleteFrom('page_person')
			->where('page_id = ?', $page);
		if(count($persons) > 0){
			foreach($persons as $person){
				if(!is_numeric($person)){
					die('hacking attempt');
				}
			}
			$q->where('person_id NOT IN ('.implode(',', $persons).')');
		}
		return $q->execute();
	}
	
	
	public function insertLink($data){
		return $this->fpdo
			->insertInto('page_link')
			->values($data)
			->execute();
	}
	
	public function updateLink($id, $data){
		return $this->fpdo
			->update('page_link', $data, $id)
			->execute();
	}
	
	public function deleteLink($page_id, $keep){
		$q = $this->fpdo
			->deleteFrom('page_link')
			->where('page_id', $page_id);
		if(is_array($keep) && count($keep) > 0){
			foreach($keep as $id){
				if(!is_numeric($id)){
					die('Hacking attempt');
				}
			}
			$q->where('page_link_id NOT IN ('.implode(',', $keep).')');
		}
		$q->execute();
	}
}
