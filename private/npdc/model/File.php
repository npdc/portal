<?php

/**
 * File model, used for data file
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\model;

class File {
    protected $dsql;
    
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
     * Get file details by id
     *
     * @param integer $id file id
     * @return array file details
     */
    public function getFile($id) {
        return \npdc\lib\Db::get('file', $id);
    }
    
    /**
     * Get draft records based on form id
     *
     * @param string $form_id id of form
     * @return array list of files
     */
    public function getDrafts($form_id) {
        return $this->dsql->dsql()
            ->table('file')
            ->where('record_state', 'draft')
            ->where('form_id', $form_id)
            ->get();
    }

    /**
     * Get number of times a file has been downloaded
     *
     * @param integer $file_id file id
     * @return integer download count
     */
    public function getDownloadCount($file_id) {
        return count($this->dsql->dsql()
            ->table('zip_files')
            ->where('file_id', $file_id)
            ->get());
    }

    /**
     * SETTERS
     */

     /**
     * Add file details to database
     *
     * @param array $data file data
     * @return integer id of newly inserted record
     */
    public function insertFile($data) {
        return \npdc\lib\Db::insert('file', $data, true);
    }
    
    /**
     * Update file details
     *
     * @param integer $id file id
     * @param array $data new details
     * @return void
     */
    public function updateFile($id, $data) {
        \npdc\lib\Db::update('file', $id, $data);
    }
    
    /**
     * delete files that are left as draft
     *
     * @param string $form_id id of form used to add file, is in form <type>/<id>
     * @return void
     */
    public function cancelDrafts($form_id) {
        $this->dsql->dsql()
            ->table('file')
            ->where('record_state', 'draft')
            ->where('form_id', $form_id)
            ->set('record_state', 'cancelled')
            ->update();
    }
}