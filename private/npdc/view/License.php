<?php
/**
 * License view
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\view;

class License {
    private $session;
    private $model;
    
    /**
     * Constructor
     *
     * @param object $session 
     */
    public function __construct($session) {
        $this->session = $session;
        $this->model = new \npdc\model\License();
    }
    
    /**
     * Show list
     *
     * @return void
     */
    public function showList() {
        $this->title = 'License';
        $this->mid = 'Not implemented';
    }
    
    /**
     * Show single license
     *
     * @param integer $id license id
     * @return void
     */
    public function showItem($id) {
        $license = $this->model->getById($id);
        $this->title = $license['license'];
        switch (NPDC_OUTPUT) {
            case 'json':
            default:
                header('Content-type:application/json;charset=utf-8');
                echo json_encode($license);
                die();
        }
    }
}