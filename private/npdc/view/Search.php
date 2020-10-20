<?php

/**
 * the view for search
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\view;

class Search extends Base{
    public $title = 'Search';
    public $left;
    public $mid;
    public $right;
    public $class = 'list search';
    public $accessLevel;
    protected $session;
    protected $controller;
    protected $userLevelAdd = NPDC_NOBODY;//minimum user level required to add a new search
    private $search;
    private $types;
    private $type = [];
    private $searchFields = [];
    
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
        if (\npdc\lib\Args::exists('types')) {
            $this->type = explode('+', \npdc\lib\Args::get('types'));
        }
        
        $this->search = str_replace('+', ' ', \npdc\lib\Args::get('subject'));
        $_SESSION[$this->controller->formId]['data']['q'] = $this->search;
        $_SESSION[$this->controller->formId]['data']['type'] = $this->type;
    }
    
    /**
     * gets the search form
     * 
     * @return string formatted form
     */
    public function getForm() {
        $formView = new \npdc\view\Form($this->controller->formId);
        $this->types = get_object_vars($this->controller->form->fields->type->options);
        if (count(get_object_vars($this->controller->form->fields->type->options)) < 2) {
            unset($this->controller->form->fields->type);
        }
        return $formView->create($this->controller->form, false);
    }
    
    /**
     * shows the search results
     * 
     * @return void
     */
    public function showList() {
        $this->left = $this->getForm();
        if (!isset($this->search) || is_null($this->search) || strlen($this->search) === 0) {
            $this->mid = 'Please provide a search term';
        } else {
            $list = [];

            //first check if result is uuid and uuid exists
            if (\Lootils\Uuid\Uuid::isValid($this->search)) {
                foreach (array_keys($this->types) as $type) {
                    $modelName = 'npdc\\model\\'.ucfirst($type);
                    $model = new $modelName();
                    $res = $model->getByUUID($this->search);
                    if (!empty($res)) {
                        header('Location: '.BASE_URL.'/'.$res['uuid']);
                        die();
                    } else {
                        $this->mid = 'No result for uuid '.$this->search;
                    }
                }
            } else {
                $this->type = count($this->type) === 0 ? array_keys($this->types) : $this->type;
                //search organizations
                if (in_array('organization', $this->type)) {
                    $this->searchFields['organization'] = [];
                    $orgModel = new \npdc\model\Organization();
                    $orgFilter = [
                        'country'=>\npdc\config::$defaultOrganizationFilter['country'],
                        'type'=>['project','dataset','publication'],
                        'combine'=>'any',
                        'search'=>$this->search
                    ];
                    $orgs = $orgModel->getList($orgFilter);
                    if (count($orgs) > 0) {
                        $this->mid .= count($orgs).' Organization'.(count($orgs) > 1 ? 's' : '').' found<ul>';
                        foreach ($orgs as $org) {
                            $this->searchFields['organization'][] = $org['organization_id'];
                            $this->mid .= '<li><a href='.BASE_URL.'/organization/'.$org['organization_id'].'>'.$org['organization_name'].'</a></li>';
                        }
                        $this->mid .= '</ul>';
                    } else {
                        $this->mid .= '<p>No organizations found</p>';
                    }
                }
                if (in_array('person', $this->type)) {
                    $this->searchFields['person'] = [];
                    $personModel = new \npdc\model\Person();
                    $persons = $personModel->search($this->search);
                    if (count($persons) > 0) {
                        $this->mid .= count($persons).' Person'.(count($persons) > 1 ? 's' : '').' found<ul>';
                        foreach ($persons as $person) {
                            $this->searchFields['person'][] = $person['person_id'];
                            $this->mid .= '<li><a href='.BASE_URL.'/contact/'.$person['person_id'].'>'.$person['name'].'</a></li>';
                        }
                        $this->mid .= '</ul>';
                    } else {
                        $this->mid .= '<p>No persons found</p>';
                    }
                }
                if (count($this->searchFields) > 0) {
                    $pubModel = new \npdc\model\Publication();
                    $personModel = new \npdc\model\Person();
                    foreach (array_diff(array_keys($this->types), ['organization', 'person']) as $type) {
                        $modelName = 'npdc\\model\\'.ucfirst($type);
                        $model = new $modelName();
                        if (array_key_exists('organization', $this->searchFields)) {
                            if (count($this->searchFields['organization']) > 0) {
                                foreach ($model->getList(['organization'=>$this->searchFields['organization']]) as $row) {
                                    $row['content_type'] = ucfirst($type);
                                    switch ($type) {
                                        case 'project':
                                        case 'dataset':
                                        $row['date'] = $row['date_start'].' - '.$row['date_end'];
                                    }
                                    $key = $type.$row[$type.'_id'];
                                    $list[$key] = $row;
                                }
                            }
                        }
                        foreach ($pubModel->searchByFreeOrganization($this->search) as $row) {
                            $row['content_type'] = 'Publication';
                            $key = 'publication'.$row['publication_id'];
                            $list[$key] = $row;
                        }
                        if (array_key_exists('person', $this->searchFields)) {
                            if (count($this->searchFields['person']) > 0) {
                                $function = 'get'.ucfirst($type).'s';
                                foreach ($this->searchFields['person'] as $id) {
                                    foreach ($personModel->{$function}($id) as $row) {
                                        $row['content_type'] = ucfirst($type);
                                        switch ($type) {
                                            case 'project':
                                            case 'dataset':
                                                $row['date'] = $row['date_start'].' - '.$row['date_end'];
                                        }
                                        $key = $type.$row[$type.'_id'];
                                        $list[$key] = $row;
                                    }
                                }
                            }
                            foreach ($pubModel->searchByFreePerson($this->search) as $row) {
                                $row['content_type'] = 'Publication';
                                $key = 'publication'.$row['publication_id'];
                                $list[$key] = $row;
                            }
                        }
                    }
                }
                $this->type = array_diff($this->type, ['organization', 'person']);
                if (count($this->type) > 0) {
                    //free text search trough dataset, project and publication
                    foreach ($this->type as $type) {
                        $modelName = 'npdc\\model\\'.ucfirst($type);
                        $model = new $modelName();
                        foreach ($model->search($this->search, true) as $row) {
                            $key = $type.$row[$type.'_id'];
                            $list[$key] = $row;
                        }
                    }
                }
                //make keys with date for ordering by date
                $keys = [];
                foreach ($list as $data) {
                    $key = $data['date'];
                    $i = 0;
                    while (in_array($key, $keys)) {
                        $i++;
                        $key = $data['date'].' '.$i;
                    }
                    $keys[] = $key;
                }
                $list = array_combine($keys, $list);
                krsort($list);

                //Display results
                $this->mid .= count($list).' result'.(count($list) === 1 ? '' : 's').' for \''.$this->search    .'\'';
                $arr = count($this->type) > 0 ? $this->type : array_keys($this->types);
                $this->mid .= ' in ';
                foreach ($arr as $i=>$type) {
                    if ($i>0 && $i<count($arr)-1) {
                        $this->mid .= ', ';
                    } elseif ($i>0) {
                        $this->mid .= ' and ';
                    }
                    $this->mid .= $this->types[$type];
                }
                if (count($list) > 0) {
                    $this->mid .= $this->displayTable('search', $list, ['content_type'=>'Type', 'title'=>'Title', 'date'=>'Date'], ['content_type', 'content_type_id'], false);
                }
            }    
        }
    }
    
    /**
     * alias of showList
     * 
     * @return void
     */
    public function showItem() {
        $this->showList();
    }
}
