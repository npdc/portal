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
    private $dsql;

    /**
     * Constructor
     */
    public function __construct() {
        $this->dsql = \npdc\lib\Db::getDSQLcon();
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
    public function getList($field) {
        return $this->dsql->dsql()
            ->table('suggestion')
            ->where('field', $field)
            ->order('suggestion')
            ->get();
    }

    /**
     * SETTERS
     * 
     * Not present, has to be updated directly in the database currently
     */
}