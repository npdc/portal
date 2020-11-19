<?php
/**
 * Person controller
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\controller;

class Person extends Base {
    public $formId = 'personlist';
    
    /**
     * Constructor
     *
     * @param object $session login information
     *
     */
    public function __construct($session) {
        $this->session = $session;
        if ($session->userLevel < NPDC_OFFICER) {
            header('Location: '.BASE_URL.'/');
            die();
        }
        if (
            \npdc\lib\Args::get('action') === 'takeover'
            && \npdc\lib\Args::get('subaction') === 'do'
            && $this->session->userLevel >= NPDC_ADMIN
        ) {
            $_SESSION['adminUser'] = $_SESSION['user'];
            $_SESSION['user']['id'] = \npdc\lib\Args::get('id');
            $_SESSION['notice'] = 'You now have taken over the rights of the '
                . 'requested user. To go back to your own account please use '
                . 'the link at the top of the page or log out and back in';
            header('Location: '.BASE_URL.'/');
            die();
        } elseif (
            \npdc\lib\Args::get('action') === 'new'
            && $this->session->userLevel < $this->userLevelAdd
        ) {
            return;
        } elseif (\npdc\lib\Args::exists('action')) {
            $id = \npdc\lib\Args::get('id');
            $this->display = 'edit_form';
            $this->formId = 'person_'.$id;
            unset($_SESSION[$this->formId]['data']);
            $this->formController = new \npdc\controller\Form($this->formId);
            $this->formController->getForm('person');
            $this->formController->form->fields->organization_id
                ->options = $this->getOrganizations();
            $this->formController->form->fields->user_level->disabled = false;
            $this->formController->form->action = $_SERVER['REQUEST_URI'];
            $this->model = new \npdc\model\Person();
            if (array_key_exists('formid', $_POST)) {
                $this->formController->doCheck();
                if ($this->formController->ok) {
                    if (
                        $this->model->checkMail(
                            $_SESSION[$this->formId]['data']['mail'],
                            \npdc\lib\Args::get('action') === 'new'
                                ? 0
                                : \npdc\lib\Args::get('id')
                        )
                    ) {
                        unset($_SESSION[$this->formId]['data']['formid']);
                        unset($_SESSION[$this->formId]['data']['submit']);
                        $data = $_SESSION[$this->formId]['data'];
                        if (\npdc\lib\Args::get('action') === 'new') {
                            $id = $this->model->insertPerson($data);
                        } else {
                            $id = \npdc\lib\Args::get('id');
                            $this->model->updatePerson(
                                $data,
                                \npdc\lib\Args::get('id')
                            );
                        }
                        $_SESSION['notice'] = 'The changes have been saved';
                        header('Location: '.BASE_URL.'/person/'.$id);
                    } else {
                        $_SESSION[$this->formId]['errors']['mail'] = 
                            'There is another person with this mail address';
                    }
                }
                $_SESSION[$this->formId]['data'] = $_POST;
            } elseif (\npdc\lib\Args::get('action') !== 'new') {
                $_SESSION[$this->formId]['data'] = $this->model->getById($id);
            }
        } elseif (!\npdc\lib\Args::exists('id')) {
            unset($_SESSION[$this->formId]['data']);
            $this->formController = new \npdc\controller\Form($this->formId);
            $this->formController->getForm('personlist');
            $this->formController->form->fields->organization
                ->options = $this->getOrganizations('person');
            
            if (array_key_exists('formid', $_GET)) {
                $this->formController->doCheck('get');
            }
        }
    }
}