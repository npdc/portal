<?php

/**
 * Login form
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\view;

class Login extends Base{
    public $title = 'Login';
    public $mid;
    public $right;
    public $class = 'login';
    public $template = 'plain';
    
    private $session;
    private $controller;
    
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
     * (Login) form/status page
     * 
     * @return void
     */
    public function showList() {
        if ($_GET['view'] === 'overlay') {
            $this->template = 'plain';
        }
        if ($this->session->userLevel === NPDC_PUBLIC) {
            if ($_GET['notice'] === 'expired') {
                $_SESSION['notice'] = 'For security reasons you have to provide your username and password again. All data you entered into the form will be saved when you log in again.';
            } elseif (!\npdc\config::$loginEnabled) {
                $this->mid = \npdc\config::$loginDisabledMessage;
                return;
            }
            $formView = new \npdc\view\Form($this->controller->formId);
            $this->mid = $formView->create($this->controller->form);
        } else {
            $this->mid = 'You are logged in as '.$this->session->name;
        }
    }

    /**
     * Request new password link or set new password using link
     * 
     * @return void
     */
    public function showItem() {
        switch (\npdc\lib\Args::get('loginaction')) {
            case 'reset':
                if (!\npdc\lib\Args::exists('loginkey')) {
                    $this->title = 'Request new password';
                } elseif (empty($this->controller->formId)) {
                    $this->title = 'Link not valid';
                    $this->mid = 'This link is not valid, either it is older than '.\npdc\config::$resetExpiryHours.' hours, already used or it didn\'t exist at all.';
                    $this->template = 'page';
                    return;
                } else {
                    $this->title = 'Set new password';
                    $this->template = 'page';
                }
                break;
        }
        $this->showList();
    }
}
