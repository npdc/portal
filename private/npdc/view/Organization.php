<?php

/**
 * Organization view
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\view;

class Organization extends Base{
    public $title;
    public $mid;
    public $right;
    public $class = 'page';
    public $accessLevel;
    public $canEdit;
    public $baseUrl;
    protected $session;
    protected $controller;
    protected $model;
    
    /**
     * Constructor
     *
     * @param object $session login information
     *
     * @param object $controller organization controller
     */

    public function __construct($session, $controller) {
        $this->session = $session;
        $this->controller = $controller;
        $this->canEdit = $session->userLevel >= NPDC_ADMIN;
        $this->baseUrl = $controller->id;
        
        $this->model = new \npdc\model\Organization();
        $this->baseUrl = \npdc\lib\Args::getBaseUrl();
    }
    
    /**
     * Show list of organizations
     *
     * @return void
     */
    public function showList() {
        $this->class = 'list';
        $this->canEdit = false;
        $this->title = 'Organizations';
        $list = $this->model->getList(
            isset($_SESSION[$this->controller->formId]['data'])
                ? $_SESSION[$this->controller->formId]['data'] 
                : null
        );
        $list = array_values($list);
        $n = count($list);
        $page = \npdc\lib\Args::get('page') ?? 1;
        $list = array_slice(
            $list,
            ($page - 1) * \npdc\config::$rowsPerPage,
            min($page * \npdc\config::$rowsPerPage, $n)
        );
        $this->makePager($n, $page);
        $this->left = parent::showFilters('organizationlist');
        $this->mid = $this->displayTable(
            'organization' 
            . (
                $this->session->userLevel >= NPDC_ADMIN
                ? ' searchbox'
                : ''
            ),
            $list
            , ['organization_name'=>'Name', 'organization_city'=>'City']
            , ['organization', 'organization_id']
        );
    }
    
    /**
     * Show organization
     *
     * @param integer|string $id organization id or new
     * @return void
     */
    public function showItem($id) {
        $this->canEdit = isset($this->session->userId) 
            && ($this->session->userLevel === NPDC_ADMIN);
        if (
            \npdc\lib\Args::get('action') === 'new'
            && $this->session->userLevel >= $this->controller->userLevelAdd
        ) {
            $this->title = 'Add organization';
        } elseif (!empty($id)) {
            $organization = $this->model->getById($id);
            $this->title = $organization['organization_name'];
        } else {
            $this->title = 'Not found';
            $this->mid .= 'The requested organization could not be found';
            http_response_code(404);
            return;
        }
        if (
            (
                $this->canEdit
                && \npdc\lib\Args::get('action') === 'edit'
            )
            || \npdc\lib\Args::get('action') === 'new'
        ) {
            $this->loadEditPage();
        } else {
            $this->data = $organization;
            $this->mid = parent::parseTemplate('organization_mid');
            $this->right = parent::parseTemplate('organization_right');
        }
    }
}
