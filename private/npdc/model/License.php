<?php

/**
 * License model
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */
namespace npdc\model;

class License {
    private $dsql;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->dsql = \npdc\lib\Db::getDSQLcon();
    }
    
    /**
     * Get list of licenses
     *
     * @return array list of licenses
     */
    public function getList(){
        return $this->dsql->dsql()->table('license')->order('sort')->get();
    }
}