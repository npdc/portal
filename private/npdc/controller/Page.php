<?php

/**
 * page controller
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\controller;

class Page extends Base{
    public $display = 'page';
    public $id;
    
    /**
     * Constructor
     *
     * @param object $session login information
     *
     */
    public function __construct($session) {
        $this->session = $session;
        $this->id = \npdc\lib\Args::get('id');
        if (\npdc\lib\Args::exists('action')) {
            switch(\npdc\lib\Args::get('action')) {
                case 'new':
                    die('Please ask the NPDC to add a new page to the database');
                    break;
                case 'edit':
                    if ($session->userLevel >= NPDC_ADMIN) {
                        $this->editPage(\npdc\lib\Args::get('id'));//load edit form
                        $this->display = 'edit';
                    } else {
                        $this->display = 'not_allowed';
                    }
            }
        }
    }
    
    /**
     * Load form for edition page
     *
     * @param string $id url of page
     * @return void
     */
    private function editPage($id) {
        $this->model = new \npdc\model\Page();
        $data = $this->model->getByUrl($id);
        $this->formId = 'page_'.$id;
        if (!empty($data)) {
            $this->formController = new \npdc\controller\Form($this->formId);
            $this->formController->getForm('page');
            $this->formController->form->action = $_SERVER['REQUEST_URI'];
            if (array_key_exists('formid', $_POST) && $_POST['formid'] === $this->formId) {
                $this->formController->doCheck('post');
                if ($this->formController->ok) {
                    if ($_SESSION[$this->formId]['data']['url'] === $data['url'] 
                        || (!in_array($_SESSION[$this->formId]['data']['url'], ['new', 'edit']) && $this->model->getByUrl($_SESSION[$this->formId]['data']['url']) === false)) {
                        //do save
                        $this->id = $data['page_id'];
                        
                        $this->model->updatePage($data['page_id'],
                            [
                                'url'=>$_SESSION[$this->formId]['data']['url'],
                                'title'=>$_SESSION[$this->formId]['data']['title'],
                                'content'=>  html_entity_decode($_SESSION[$this->formId]['data']['content']),
                                'show_last_revision' =>$_SESSION[$this->formId]['data']['show_last_revision'] === 'on' ? 1 : 0
                            ]
                        );

                        $this->savePeople();
                        $this->saveLinks();
                        
                        $_SESSION['notice'] = 'The page has been saved';
                        header('Location: '.BASE_URL.'/'.$_SESSION[$this->formId]['data']['url']);
                        die();
                    } else {
                        $_SESSION[$this->formId]['errors']['url'] = 'This url already exists or is not permitted';
                    }
                }
            } else {
                //Load data
                $_SESSION[$this->formId]['data'] = $data;
                $people = $this->model->getPersons($data['page_id']);
                foreach($people as $n=>$person) {
                    $_SESSION[$this->formId]['data']['people_person_id_'.$n] = $person['person_id'];
                    $_SESSION[$this->formId]['data']['people_name_'.$n] = $person['name'];
                    $_SESSION[$this->formId]['data']['people_role_'.$n] = $person['role'];
                    $_SESSION[$this->formId]['data']['people_editor_'.$n] = $person['editor'];
                }
                $urls = $this->model->getUrls($data['page_id']);
                foreach($urls as $n=>$url) {
                    $_SESSION[$this->formId]['data']['links_id_'.$n] = $url['page_link_id'];
                    $_SESSION[$this->formId]['data']['links_url_'.$n] = $url['url'];
                    $_SESSION[$this->formId]['data']['links_label_'.$n] = $url['text'];
                }
            }
        }
    }
    
    /**
     * Save people linked to page
     *
     * @return void
     */
    private function savePeople() {
        $persons = [];
        $loopId = 'people_person_id_';
        $sort = 1;
        foreach(array_keys($_SESSION[$this->formId]['data']) as $key) {
            if (substr($key, 0, strlen($loopId)) === $loopId) {
                $persons[] = $_SESSION[$this->formId]['data'][$key];
                $record = [
                    'page_id'=>$this->id, 
                    'person_id'=>$_SESSION[$this->formId]['data'][$key]
                ];
                $data = [
                    'role'=>$_SESSION[$this->formId]['data']['people_role_'.substr($key, strlen($loopId))],
                    'editor'=> !empty($_SESSION[$this->formId]['data']['people_editor_'.substr($key, strlen($loopId))]) ? 1 : 0,
                    'sort'=>$sort
                ];
                if (strpos($key, '_new_') === false) {
                    $this->model->updatePerson($record, $data);
                } else {
                    $data = array_merge($data, $record);
                    $this->model->insertPerson($data);
                }
            }
            $sort++;
        }
        $this->model->deletePerson($this->id, $persons);
    }
    
    /**
     * Save links for page
     *
     * @return void
     */
    private function saveLinks() {
        $loopId = 'links_id_';
        $keep = [];
        foreach(array_keys($_SESSION[$this->formId]['data']) as $key) {
            if (substr($key, 0, strlen($loopId)) === $loopId 
                && strpos($key, '_new_') === false) {
                $keep[] = $_SESSION[$this->formId]['data'][$key];
            }
        }
        $this->model->deleteLink($this->id, $keep);
        
        $loopId = 'links_url_';
        $sort = 1;
        foreach(array_keys($_SESSION[$this->formId]['data']) as $key) {
            if (substr($key, 0, strlen($loopId)) === $loopId) {
                $data = [];
                $data['url'] = $_SESSION[$this->formId]['data'][$key];
                $data['text'] = $_SESSION[$this->formId]['data']['links_label_'.substr($key, strlen($loopId))];
                $data['sort'] = $sort;
                $sort++;
                if (strpos($key, '_new_') === false) {
                    $recordId = $_SESSION[$this->formId]['data']['links_id_'.substr($key, strlen($loopId))];
                    $this->model->updateLink($recordId, $data);
                } else {
                    $data['page_id'] = $this->id;
                    $this->model->insertLink($data);
                }
            }
        }
    }
}
