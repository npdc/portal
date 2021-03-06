<?php
/**
 * Account view
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\view;

class Account extends Base {
    public $class = 'detail';
    public $right = '<ul>'
        . '<li><a href="' . BASE_URL . '/account">View account</a></li>'
        . '<li><a href="' . BASE_URL . '/account/edit">Edit details</a></li>'
        . '<li><a href="' . BASE_URL . '/account/password">Change password</a></li>'
        . '</ul>';
    
    /**
     * Constructor
     *
     * @param object $session login information
     *
     * @param object $controller account controller
     */
    public function __construct($session, $controller) {
        $this->session = $session;
        $this->controller = $controller;
        $this->extraHeader = '<meta name="robots" content="noindex">';
    }
    
    /**
     * Display account info
     *
     * @return void
     */
    public function showList() {
        $this->title = 'Account';
        $this->model = new \npdc\model\Person();
        $this->data = $this->model->getById($this->session->userId);
        $this->mid = parent::parseTemplate('person_mid');
        // TODO: build new template for showing org details of account page
        // $this->right .= parent::parseTemplate('organization_mid');
    }
    
    /**
     * Display edit page
     *
     * @param string $item page to display
     * @return void
     */
    public function showItem($item) {
        switch (\npdc\lib\Args::get('action')) {
            case 'edit':
                $this->title = 'Edit details';
                break;
            case 'password':
                $this->title = 'Edit password';
        }
        $this->loadEditPage();
        $this->class = 'detail edit';
    }
}