<?php

/**
 * Zip model
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\model;

class Zip {
    /**
     * GETTERS
     */

    /**
     * Get zip details by id
     *
     * @param integer $id zip id
     * @return array zip details
     */
    public function getById($id) {
        return \npdc\lib\Db::get('zip', $id);
    }

    /**
     * Get zip details by filename
     *
     * @param string $name filename
     * @return array zip details
     */
    public function getByName($name) {
        return \npdc\lib\Db::get('zip', ['filename'=>$name]);
    }

    /**
     * SETTERS
     */

    /**
     * Add new zip
     *
     * @param array $data zip details
     * @return integer id of newly inserted zip
     */
    public function insertZip($data) {
        return \npdc\lib\Db::insert('zip', $data, true);
    }
    
    /**
     * Update zip details
     *
     * @param integer $id zip id
     * @param array $data new zip details
     * @return voi
     */
    public function updateZip($id, $data) {
        \npdc\lib\Db::update('zip', $id, $data);
    }
    
    /**
     * Add file to zip
     *
     * @param array $data new record
     * @return void
     */
    public function insertFile($data) {
        \npdc\lib\Db::insert('zip_files', $data);
    }
}