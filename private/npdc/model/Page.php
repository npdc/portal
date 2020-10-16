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
    private $dsql;

    /**
     * Constructor
     */
    public function __construct() {
        $this->dsql = \npdc\lib\Db::getDSQLcon();
    }
    
    /**
     * get a page by its url
     * 
     * @param string $url url of the page
     * @return array page
     */
    public function getByUrl($url) {
        return $this->dsql->dsql()
            ->table('page')
            ->where('url', $url)
            ->get()[0];
    }
    
    /**
     * get persons linked to a page
     * 
     * @param integer $id page id
     * @return array list of persons
     */
    public function getPersons($id) {
        return $this->dsql->dsql()
            ->table('page_person')->field('*')
            ->join('person.person_id', 'person_id', 'inner')
            ->join('organization.organization_id', 'person.organization_id')
            ->where('page_id', $id)
            ->order('sort')
            ->get();
    }
    
    /**
     * get urls linked to a page
     * 
     * @param intger $id page id
     * @return array list of urls
     */
    public function getUrls($id) {
        return $this->dsql->dsql()
            ->table('page_link')
            ->where('page_id', $id)
            ->order('sort')
            ->get();
    }

    public function getList() {
        return $this->dsql->dsql()->table('page')->order('title')->get();
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
    public function updatePage($id, $values) {
        $values['last_update'] = date('Y-m-d h:i:s');
        return \npdc\lib\Db::update('page', $id, $values);
    }
    
    /**
     * Add person to page
     *
     * @param array $data record to be added
     * @return boolean succesfully inserted
     */
    public function insertPerson($data) {
        return \npdc\lib\Db::insert('page_person', $data);
    }
    
    /**
     * Update linked person
     *
     * @param array $record identification of record
     * @param array $data new data to put in record
     * @return boolean update succesfull
     */
    public function updatePerson($record, $data) {
        \npdc\lib\Db::update('page_person', $record, $data);
    }
    
    /**
     * Remove linked person
     *
     * @param integer $page page id
     * @param array $persons id's of persons NOT to delete
     * @return boolean update succesfull
     */
    public function deletePerson($page, $persons) {
        $q = $this->dsql->dsql()
            ->table('page_person')
            ->where('page_id', $page);
        if (count($persons) > 0) {
            foreach($persons as $person) {
                if (!is_numeric($person)) {
                    die('hacking attempt');
                }
            }
            $q->where('person_id NOT IN ('.implode(',', $persons).')');
        }
        return $q->delete();
    }
    
    
    /**
     * Add link to page
     *
     * @param array $data record to be added
     * @return boolean succesfully inserted
     */
    public function insertLink($data) {
        return \npdc\lib\Db::insert('page_link', $data);
    }
    
    /**
     * Update link
     *
     * @param array $id page_link id
     * @param array $data new data to put in record
     * @return boolean update succesfull
     */
    public function updateLink($id, $data) {
        return \npdc\lib\Db::update('page_link', $id, $data);
    }
    
    /**
     * Delete links from page
     *
     * @param integer $page_id page id
     * @param array $keep id's of links NOT to delete
     * @return boolean update succesfull
     */
    public function deleteLink($page_id, $keep) {
        $q = $this->dsql->dsql()
            ->table('page_link')
            ->where('page_id', $page_id);
        if (is_array($keep) && count($keep) > 0) {
            foreach($keep as $id) {
                if (!is_numeric($id)) {
                    die('Hacking attempt');
                }
            }
            $q->where('page_link_id NOT IN ('.implode(',', $keep).')');
        }
        $q->delete();
    }
}
