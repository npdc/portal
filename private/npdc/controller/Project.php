<?php

/**
 * Project controller
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\controller;

class Project extends Base{
    public $formId = 'projectlist';
    public $name = 'project';
    public $userLevelAdd = NPDC_EDITOR;
    
    /**
     * Constructor
     *
     * @param object $session login information
     *
     */
    public function __construct($session) {
        $this->session = $session;
        $this->model = new \npdc\model\Project();
        parent::__construct();
    }

    /**
     * Check if draft is different from published version
     *
     * @param integer $id record id
     * @param integer $version version number of draft
     * @return boolean did record change
     */
    public function recordChanged($id, $version) {
        if($this->generalHasChanged($id, $version)){
            return true;
        }
        $tables = [
            'project_keyword',
            'project_link',
            'project_person',
            'project_publication',
            'dataset_project'
        ];
        foreach ($tables as $table) {
            if($this->tblHasChanged($table, $id, $version)){
                return true;
            }
        }
        return false;
    }

    /**
     * Populate program and organization fields
     *
     * @return void
     */
    protected function alterFields() {
        $this->formController->form->fields->npp_theme_id
            ->options = $this->getNppThemes();
        $this->formController->form->fields->program_id
            ->options = $this->getPrograms();
        $this->formController->form->fields->people->fields->organization_id
            ->options = $this->getOrganizations();
    }
    
    /**
     * Populate form with record data
     *
     * @param array $baseData
     * @return void
     */
    protected function loadForm($baseData) {
        if (\npdc\lib\Args::get('action') === 'new') {
            unset($_SESSION[$this->formId]);
            $this->setFormData(
                'people',
                [
                    'person_id' => $this->session->userId, 
                    'name' => $this->session->name, 
                    'organization_id' => $this->session->organization_id, 
                    'editor' => true, 
                    'contact' => true, 
                    'role' => 'PI'
                ],
                true
            );
        } else {
            $_SESSION[$this->formId]['data'] = $baseData;

            $keywords = $this->model->getKeywords($this->id, $this->version);
            $words = [];
            foreach ($keywords as $keyword) {
                $words[] = $keyword['keyword'];
            }
            $this->setFormData('keywords', $words);

            $links = $this->model->getLinks($this->id, $this->version);
            foreach ($links as $n=>$link) {
                $this->setFormData('links_id_' . $n, $link['project_link_id']);
                $this->setFormData('links_url_' . $n,  $link['url']);
                $this->setFormData('links_label_' . $n, $link['text']);
            }
            $this->setFormData(
                'people',
                $this->model->getPersons($this->id, $this->version)
            );
            $this->setFormData(
                'period',
                [$baseData['date_start'], $baseData['date_end']]
            );
            $this->setFormData(
                'datasets',
                $this->model->getDatasets($this->id, $this->version)
            );
            $this->setFormData(
                'publications',
                $this->model->getPublications($this->id, $this->version)
            );
        }
    }

    private function getNppThemes() {
        $model = new \npdc\model\Npp_theme();
        $return = [];
        foreach ($model->getList() as $theme) {
            $return[$theme['npp_theme_id']] = $theme['npp_theme_id'].'. '.$theme['theme_en'];
        }
        return $return;
    }
    /**
     * Save record to database
     *
     * @return void
     */
    protected function doSave() {
        if (\npdc\lib\Args::get('action') === 'new') {
            $this->version = 1;
        }
            
        if ($_SESSION[$this->formId]['db_action'] === 'insert') {
            $this->setFormData('project_version', $this->version);
            $this->setFormData('record_status', 'draft');
            $this->setFormData('creator', $this->session->userId);
            $this->id = $this->model->insertGeneral(
                $_SESSION[$this->formId]['data']
            );
        } else {
            $saved = $this->model->updateGeneral(
                $_SESSION[$this->formId]['data'],
                $this->id,
                $this->version
            ) !== false;
        }
        $this->saveKeywords();
        $this->saveLinks();
        $this->savePublications();
        $this->saveDatasets();

        $saved = $this->savePeople();

        $_SESSION['notice'] = $saved 
            ? '<p>Your changes have been saved.</p>' 
            : 'Something went wrong when trying to save your record';
        if ($saved) {
            unset($_SESSION[$this->formId]);
            header(
                'Location: ' . BASE_URL . '/project/' . $this->id . '/' 
                . $this->version
            );
            echo 'redirect';
            die();
        }
    }
    
    /**
     * Save keywords
     * 
     * @return void
     */
    private function saveKeywords() {
        $currentKeywords = $this->model->getKeywords($this->id, $this->version);
        $words = [];
        foreach ($currentKeywords as $row) {
            $words[] = $row['keyword'];
        }
        $new = array_diff($this->getFormData('keywords'), $words);
        $old = array_diff($words, $this->getFormData('keywords'));
        if (count($old) > 0) {
            foreach ($old as $word) {
                $this->model->deleteKeyword($word, $this->id, $this->version-1);
            }
        }
        if (count($new) > 0) {
            foreach ($new as $word) {
                $this->model->insertKeyword($word, $this->id, $this->version);
            }
        }
    }
    
    /**
     * Save links
     * 
     * @return void
     */
    private function saveLinks() {
        $loopId = 'links_id_';
        $keep = [];
        foreach (array_keys($_SESSION[$this->formId]['data']) as $key) {
            if (substr($key, 0, strlen($loopId)) === $loopId 
                && strpos($key, '_new_') === false) {
                $keep[] = $this->getFormData($key);
            }
        }
        $this->model->deleteLink($this->id, $this->version-1, $keep);
        
        $loopId = 'links_url_';
        foreach (array_keys($_SESSION[$this->formId]['data']) as $key) {
            if (substr($key, 0, strlen($loopId)) === $loopId) {
                $data = [];
                $data['url'] = $this->getFormData($key);
                $data['text'] = $this->getFormData(
                    'links_label_' . substr($key, strlen($loopId))
                );
                if (strpos($key, '_new_') === false) {
                    $recordId = $this->getFormData(
                        'links_id_' . substr($key, strlen($loopId))
                    );
                    $this->model->updateLink($recordId, $data, $this->version);
                } else {
                    $data['project_id'] = $this->id;
                    $data['project_version_min'] = $this->version;
                    $this->model->insertLink($data);
                }
            }
        }
    }
    
    /**
     * Link (bi-directional) to data set
     *
     * @return void
     */
    private function saveDatasets() {
        $current = [];
        $loopId = 'datasets_dataset_id_';
        $datasetModel = new \npdc\model\Dataset();
        
        foreach (array_keys($_SESSION[$this->formId]['data']) as $key) {
            if (substr($key, 0, strlen($loopId)) === $loopId) {
                $current[] = $this->getFormData($key);
                if (strpos($key, '_new_') !== false) {
                    $data = [
                        'project_id' => $this->id, 
                        'dataset_id' => $this->getFormData($key),
                        'dataset_version_min' =>
                            $datasetModel->getVersions(
                                $this->getFormData($key)
                            )[0]['dataset_version'],
                        'project_version_min' => $this->version
                    ];
                    $this->model->insertDataset($data);
                }
            }
        }
        $v = $this->version-1;
        $this->model->deleteDataset($this->id, $v, $current);
    }

    /**
     * Link (bi-directional) to publication
     *
     * @return void
     */
    private function savePublications() {
        $current = [];
        $loopId = 'publications_publication_id_';
        $publicationModel = new \npdc\model\Publication();
        
        foreach (array_keys($_SESSION[$this->formId]['data']) as $key) {
            if (substr($key, 0, strlen($loopId)) === $loopId) {
                $current[] = $this->getFormData($key);
                if (strpos($key, '_new_') !== false) {
                    $data = [
                        'project_id' => $this->id, 
                        'publication_id' => $this->getFormData($key),
                        'publication_version_min' =>
                            $publicationModel->getVersions(
                                $this->getFormData($key)
                            )[0]['publication_version'],
                        'project_version_min' => $this->version
                    ];
                    $this->model->insertPublication($data);
                }
            }
        }
        $v = $this->version-1;
        $this->model->deletePublication($this->id, $v, $current);
    }

    /**
     * Link to people
     *
     * @return void
     */
    private function savePeople() {
        $persons = [];
        $loopId = 'people_person_id_';
        $sort = 1;
        foreach (array_keys($_SESSION[$this->formId]['data']) as $key) {
            if (substr($key, 0, strlen($loopId)) === $loopId) {
                $persons[] = $this->getFormData($key);
                $record = [
                    'project_id' => $this->id, 
                    'person_id' => $this->getFormData($key),
                    'project_version_max' => null
                ];
                $data = [];
                $data['organization_id'] = $this->getFormData(
                    'people_organization_id_' . substr($key, strlen($loopId))
                );
                $data['role'] = $this->getFormData(
                    'people_role_' . substr($key, strlen($loopId))
                );
                $data['contact'] = !empty($this->getFormData(
                        'people_contact_' . substr($key, strlen($loopId))
                    ))
                    ? 1
                    : 0;
                $data['editor'] = !empty($this->getFormData(
                        'people_editor_' . substr($key, strlen($loopId))
                    ))
                    ? 1
                    : 0;
                $data['sort'] = $sort;
                if (strpos($key, '_new_') === false) {
                    $saved = $this->model->updatePerson(
                            $record,
                            $data,
                            $this->version
                        ) === false ? false : $saved;
                } else {
                    $data = array_merge(
                        $data,
                        $record,
                        ['project_version_min'=>$this->version]
                    );
                    $this->model->insertPerson($data);
                }
                $sort++;
            }
        }
        $v = $this->version-1;
        return $this->model->deletePerson($this->id, $v, $persons) !== false;
    }
}