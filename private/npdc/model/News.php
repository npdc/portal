<?php

/**
 * News model
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */
namespace npdc\model;

class News {
    private $dsql;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->dsql = \npdc\lib\Db::getDSQLcon();
    }
    
    /**
     * Get list of news items
     *
     * @return array list of news items
     */
    public function getList() {
        return $this->dsql->dsql()->table('news')->get();
    }
    
    /**
     * Get news item by id
     *
     * @param integer $id news item id
     * @return array news item
     */
    public function getById($id) {
        return \npdc\lib\Db::get('news', $id);
    }
    
    /**
     * Get n news items that haven't expired
     * 
     * For use in front page
     *
     * @param integer $n number of items to show
     * @return array news items
     */
    public function getLatest($n = 1) {
        return $this->dsql->dsql()
            ->table('news')
            ->where('published < CURRENT_TIMESTAMP')
            ->where([
                ['show_till IS NULL'], 
                ['show_till > CURRENT_TIMESTAMP']
            ])
            ->limit($n)
            ->order('published DESC')
            ->get();
    }
    
}