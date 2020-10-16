<?php

/**
 * Funding program controller
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\model;

class Program {
    private $dsql;

    /**
     * Constructor
     */
    public function __construct() {
        $this->dsql = \npdc\lib\Db::getDSQLcon();
    }
    
    /**
     * List programs
     *
     * @param string $filter (optional) only show when present in specified content type
     * @return array list of programs
     */
    public function getList($filter = null) {
        $q = $this->dsql->dsql()
            ->table('program')
            ->order('sort');
        if (!is_null($filter)) {
            $q2 = null;
            switch($filter) {
                case 'project':
                    $q2 = $q->dsql()
                        ->table('project','a');
                    break;
                case 'publication':
                    $q2 = $q->dsql()
                        ->table('project_publication')
                        ->join('publication.publication_id a','publication_id', 'inner')
                        ->join('project.project_id b','project_id', 'inner')
                        ->where('b.record_status', 'published');
                    
                    break;
            }
            if (!is_null($q2)) {
                $q2->field('DISTINCT(program_id)')
                    ->where('a.record_status', 'published');
                $q->where('program_id', $q2);
            }
        }
        return $q->get();
    }
    
    /**
     * Get program details
     *
     * @param integer $id program id
     * @return array program details
     */
    public function getById($id) {
        return \npdc\lib\Db::get('program', $id);
    }

    /**
     * Add new program
     *
     * @param array $data Program details to insert
     * @return boolean insert succesfull
     */
    public function insertProgram($data) {
        \npdc\lib\Db::insert('program', $data);
    }
    
    /**
     * Update program
     *
     * @param array $data new details
     * @param integer $id program id
     * @return boolean update successfull
     */
    public function updateProgram($data, $id) {
        \npdc\lib\Db::update('program', $id, $data);
    }
}