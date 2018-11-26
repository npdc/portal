<?php

/**
 * page model
 * 
 * retrieve info pages from the db
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\model;

class Page{
	private $fpdo;

	/**
	 * Constructor
	 */
	public function __construct(){
		$this->fpdo = \npdc\lib\Db::getFPDO();
	}
	
	/**
	 * get a page by its url
	 * 
	 * @param string $url url of the page
	 * @return array page
	 */
	public function getByUrl($url){
		return $this->fpdo
			->from('page')
			->where('url', $url)
			->fetch();
	}
	
	/**
	 * get persons linked to a page
	 * 
	 * @param integer $id page id
	 * @return array list of persons
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
	 * get urls linked to a page
	 * 
	 * @param intger $id page id
	 * @return array list of urls
	 */
	public function getUrls($id){
		return $this->fpdo
			->from('page_link')
			->where('page_id', $id)
			->orderBy('sort')
			->fetchAll();
	}

	public function getList(){
		return $this->fpdo->from('page')->orderBy('title')->fetchAll();
	}
	
	/**
	 * SETTERS
	 * 
	 * insertPage is missing, also in the interface. Only way to add a new page is to add it to the database directly
	 */


	/**
	 * update a page
	 *
	 * @param integer $id page id
	 * @param array $values new data of page
	 * @return void
	 */
	public function updatePage($id, $values){
		$values['last_update'] = date('Y-m-d h:i:s');
		return $this->fpdo
			->update('page', $values, $id)
			->execute();
	}
	
	/**
	 * Add person to page
	 *
	 * @param array $data record to be added
	 * @return boolean succesfully inserted
	 */
	public function insertPerson($data){
		return $this->fpdo
			->insertInto('page_person')
			->values($data)
			->execute();
	}
	
	/**
	 * Update linked person
	 *
	 * @param array $record identification of record
	 * @param array $data new data to put in record
	 * @return boolean update succesfull
	 */
	public function updatePerson($record, $data){
		return $this->fpdo
			->update('page_person')
			->set($data)
			->where($record)
			->execute();
	}
	
	/**
	 * Remove linked person
	 *
	 * @param integer $page page id
	 * @param array $persons id's of persons NOT to delete
	 * @return boolean update succesfull
	 */
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
	
	
	/**
	 * Add link to page
	 *
	 * @param array $data record to be added
	 * @return boolean succesfully inserted
	 */
	public function insertLink($data){
		return $this->fpdo
			->insertInto('page_link')
			->values($data)
			->execute();
	}
	
	/**
	 * Update link
	 *
	 * @param array $id page_link id
	 * @param array $data new data to put in record
	 * @return boolean update succesfull
	 */
	public function updateLink($id, $data){
		return $this->fpdo
			->update('page_link', $data, $id)
			->execute();
	}
	
	/**
	 * Delete links from page
	 *
	 * @param integer $page_id page id
	 * @param array $keep id's of links NOT to delete
	 * @return boolean update succesfull
	 */
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
