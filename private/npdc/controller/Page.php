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
                        $this->editPage(\npdc\lib\Args::get('id'));
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
            if (
                array_key_exists('formid', $_POST)
                && $_POST['formid'] === $this->formId
            ) {
                $this->formController->doCheck('post');
                if ($this->formController->ok) {
                    if (
                        $this->getFormData('url') === $data['url'] 
                        || (
                            !in_array(
                                $this->getFormData('url'),
                                ['new', 'edit']
                            ) 
                            && $this->model->getByUrl(
                                $this->getFormData('url')
                            ) === false
                        )
                    ) {
                        //do save
                        $this->id = $data['page_id'];
                        
                        $this->model->updatePage(
                            $data['page_id'],
                            [
                                'url' => $this->getFormData('url'),
                                'title' => $this->getFormData('title'),
                                'content' =>  html_entity_decode(
                                    $this->getFormData('content')
                                ),
                                'show_last_revision' =>
                                    $this->getFormData('show_last_revision') === 'on'
                                    ? 1
                                    : 0
                            ]
                        );

                        $this->savePeople();
                        $this->saveLinks();
                        
                        $_SESSION['notice'] = 'The page has been saved';
                        header('Location: ' . BASE_URL . '/' . $this->getFormData('url'));
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
                    $this->setFormData(
                        'people_person_id_' . $n,
                        $person['person_id']
                    );
                    $this->setFormData(
                        'people_name_' . $n,
                        $person['name']
                    );
                    $this->setFormData(
                        'people_role_' . $n,
                        $person['role']
                    );
                    $this->setFormData(
                        'people_editor_' . $n,
                        $person['editor']
                    );
                }
                $urls = $this->model->getUrls($data['page_id']);
                foreach($urls as $n=>$url) {
                    $this->setFormData(
                        'links_id_' . $n,
                        $url['page_link_id']
                    );
                    $this->setFormData(
                        'links_url_' . $n,
                        $url['url']
                    );
                    $this->setFormData(
                        'links_label_' . $n,
                        $url['text']
                    );
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
                $persons[] = $this->getFormData($key);
                $record = [
                    'page_id'=>$this->id, 
                    'person_id'=>$this->getFormData($key)
                ];
                $data = [
                    'role'=>$this->getFormData('people_role_' . substr($key, strlen($loopId))),
                    'editor'=> !empty($this->getFormData('people_editor_'.substr($key, strlen($loopId)))) ? 1 : 0,
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
                $keep[] = $this->getFormData($key);
            }
        }
        $this->model->deleteLink($this->id, $keep);
        
        $loopId = 'links_url_';
        $sort = 1;
        foreach(array_keys($_SESSION[$this->formId]['data']) as $key) {
            if (substr($key, 0, strlen($loopId)) === $loopId) {
                $data = [];
                $data['url'] = $this->getFormData($key);
                $data['text'] = $this->getFormData(
                    'links_label_' . substr($key, strlen($loopId))
                );
                $data['sort'] = $sort;
                $sort++;
                if (strpos($key, '_new_') === false) {
                    $recordId = $this->getFormData(
                        'links_id_' . substr($key, strlen($loopId))
                    );
                    $this->model->updateLink($recordId, $data);
                } else {
                    $data['page_id'] = $this->id;
                    $this->model->insertLink($data);
                }
            }
        }
    }
}
