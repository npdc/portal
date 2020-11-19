<?php

/**
 * base controller
 * 
 * helpers for project, dataset and publication
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\controller;

class Base {
    public $formId;
    protected $name;
    public $access = true;
    public $draftMsg;
    public $display;
    public $formController;
    protected $session;
    protected $model;
    protected $version;
    
    /**
     * constructor
     */
    public function __construct() {
        if (\npdc\config::$reviewBeforePublish) {
            $this->draftMsg = ', please remember to submit the record for review when you are done. After clicking the button you can add comments to the reviewer.<br/><button onclick="openUrl(\'' . BASE_URL . '/' . \npdc\lib\Args::get('type') . '/' . \npdc\lib\Args::get('id') . '/submit\')">Submit</button>';
            $this->submitForm = 'If there is anything you want the reviewer to look at or take into consideration please put it in the field below.<textarea id="comment" placeholder="Comments for reviewer"></textarea>Are you sure you want to submit this record for review? <button onclick="window.location=\'dosubmit?comment=\'+encodeURIComponent($(\'#comment\').val())">Yes</button> <button onclick="window.location=\'draft\'">No</button>';
            $this->publishForm = 'This record is submitted for review. If everything is correct please <button onclick="window.location=\'publish?comment=\'+encodeURIComponent($(\'#comment\').val())">Publish</button>, otherwise please provide a reason<textarea id="comment" placeholder="Reason for rejection"></textarea> and <button onclick="window.location=\'cancel?comment=\'+encodeURIComponent($(\'#comment\').val())">Reject</button>';
        } else {
            $this->draftMsg = ', please remember to publish the record when you are done.<br/><button onclick="openUrl(\'' . BASE_URL . '/' . \npdc\lib\Args::get('type') . '/' . \npdc\lib\Args::get('id') . '/publish\')">Publish</button>';
        }
        if (
            !\npdc\lib\Args::exists('id')
            && !\npdc\lib\Args::exists('action')
        ) {
            unset($_SESSION[$this->formId]['data']);
            $this->formController = new \npdc\controller\Form($this->formId);
            $this->formController->getForm($this->name.'list');
            if (
                $this->session->userLevel >= NPDC_USER 
                && count($this->model->getList(['editorOptions'=>['edit']]))>0
            ) {
                $this->formController->form->fields->editorOptions
                    ->disabled = false;
            }
            $this->formController->form->fields->program->options = 
                $this->getPrograms($this->name);
            $this->formController->form->fields->organization->options = 
                $this->getOrganizations($this->name);
            $this->formController->form->action = 
                BASE_URL . '/' . $this->formController->form->action;
            

            #check if data is sent
            if (array_key_exists('formid', $_GET)) {
                $this->formController->doCheck('get');
            }
        } else {
            if (
                \npdc\lib\Args::exists('action') 
                && in_array(
                    \npdc\lib\Args::get('action'), 
                    [
                        'submit',
                        'edit',
                        'dosubmit',
                        'publish',
                        'cancel',
                        'warnings',
                        'new'
                    ]
                )
            ) {
                $this->access = (
                    $this->session->userLevel >= NPDC_OFFICER 
                    || $this->model->isEditor(
                        \npdc\lib\Args::get('id'),
                        $this->session->userId
                    ) 
                    || (
                        \npdc\lib\Args::get('action') === 'new' 
                        && $this->session->userLevel >= $this->userLevelAdd
                    )
                );
                $doSwitch = true;
                if ($this->access) {
                    if (
                        in_array(
                            \npdc\lib\Args::get('action'),
                            ['submit', 'dosubmit', 'publish', 'warnings']
                        )
                    ) {
                        $doSwitch = false;
                        if (!property_exists($this, 'pages')) {
                            $this->pages = ['general'=>'Edit'];
                        }
                        $errors = [];
                        $this->id = \npdc\lib\Args::get('id');
                        $baseData = $this->model->getById($this->id, 'draft');
                        if (empty($baseData)) {
                            header(
                                'Location: ' . BASE_URL . '/' . $this->name
                                . '/' . $this->id
                            );
                            die();
                        }
                        $this->version = $baseData[$this->name.'_version'];
                        $_SESSION['warnings'] = [];
                        foreach ($this->pages as $page=>$title) {
                            $this->screen = $page;
                            $this->formController = new \npdc\controller\Form(
                                $this->name . '_' . $this->id
                            );
                            $this->formController->getForm(
                                $this->name.'_'.$page
                            );
                            $this->alterFields();
                            $this->loadForm($baseData);
                            $this->formController->doCheck(
                                $_SESSION[$this->formId]['data']
                            );
                            if (count($this->formController->errors) > 0) {
                                $_SESSION['warnings'][$page] = [
                                    $title,
                                    $this->formController->errors
                                ];
                            }
                        }
                        if (
                            count($_SESSION['warnings']) === 0
                            || (
                                \npdc\lib\Args::get('action') === 'publish' 
                                && $this->session->userLevel >= NPDC_ADMIN 
                                && array_key_exists('adminoverrule', $_GET) 
                                && $_GET['adminoverrule'] === 'adminoverrule'
                            )
                        ) {
                            unset($_SESSION['warnings']);
                            $doSwitch = true;
                        } elseif (
                            \npdc\lib\Args::get('action') !== 'warnings'
                        ) {
                            header('Location: warnings');
                            die();
                        }
                    }
                }
                
                if (!$this->access) {
                    $this->display = 'not_allowed';
                } elseif (!$doSwitch) {
                    $this->display = 'errors';
                } else {
                    $this->doAction();
                }
            }
        }
    }
    
    protected function setFormData($label, $value, $append = false){
        if($append){
            $_SESSION[$this->formId]['data'][$label][] = $value;
        } else {
            $_SESSION[$this->formId]['data'][$label] = $value;
        }
    }
    
    protected function getFormData($label){
        return $_SESSION[$this->formId]['data'][$label];
    }
    
    protected function getSerial($key, $loopId, $sep = '_'){
        return substr(
            $key, 
            strlen($loopId), 
            strpos(
                $key, $sep, strlen($loopId)
            )-strlen($loopId)
        );
    }
    
    private function doAction(){
        switch (\npdc\lib\Args::get('action')) {
            case 'dosubmit':
                $data = $this->model->getById(
                    \npdc\lib\Args::get('id'),
                    'draft'
                );
                if (empty($data)) {
                    $_SESSION['notice'] = 'No draft was found';
                } else {
                    $this->model->setStatus(
                        \npdc\lib\Args::get('id'),
                        'draft',
                        'submitted',
                        $_GET['comment']
                    );
                    $_SESSION['notice'] = 
                        'You record has been submitted for review';
                    //send mail to NPDC
                    $mail = new \npdc\lib\Mailer();
                    $mail->to(
                        \npdc\config::$mail['contact'],
                        \npdc\config::$siteName
                    );
                    $mail->subject(ucfirst($this->name).' for review');
                    $text = 'Dear admin'.",\r\n\r"
                        . 'Please review the ' . $this->name . ' \''
                        . $data['title'] . '\' at ' . $_SERVER['REQUEST_SCHEME']
                        . '://' . $_SERVER['HTTP_HOST'] . BASE_URL . '/'
                        . $this->name . '/' . \npdc\lib\Args::get('id')
                        . "\r\n\r\nKind regards,\r\n" . \npdc\config::$siteName;

                    $mail->text($text);
                    $mail->send();
                }
                \npdc\view\Base::checkUnpublished();
                header(
                    'Location: ' . BASE_URL . '/' . $this->name . '/'
                    . \npdc\lib\Args::get('id') . '/submitted');
                die();
            case 'publish':
                $prevState = (
                    \npdc\config::$reviewBeforePublish
                    ? 'submitted'
                    : 'draft'
                );
                $data = $this->model->getById(
                    \npdc\lib\Args::get('id'),
                    $prevState
                );
                if (empty($data)) {
                    $_SESSION['notice'] = 'No version foud to publish';
                } elseif (
                    $this->session->userLevel < NPDC_ADMIN 
                    && \npdc\config::$reviewBeforePublish
                ) {
                    $_SESSION['notice'] = 
                        'You have insufficient rights to publish';
                } else {
                    $this->model->setStatus(
                        \npdc\lib\Args::get('id'),
                        'published',
                        'archived'
                    );
                    $this->model->setStatus(
                        \npdc\lib\Args::get('id'),
                        $prevState,
                        'published',
                        $_GET['comment']
                    );
                    $_SESSION['notice'] = 'The record has been published';
                    $r = $this->model->getById(\npdc\lib\Args::get('id'));
                    \npdc\lib\Push::send(
                        (
                            '['
                            . (
                                $data[$this->name.'_version'] === 1
                                ? 'New'
                                : 'Updated'
                            )
                            . ' ' . $this->name . '] ' . $data['title']
                        ),
                        (
                            $_SERVER['REQUEST_SCHEME'] . '://'
                            . $_SERVER['HTTP_HOST'] . BASE_URL . '/'
                            . $this->name . '/' . $r['uuid']
                        )
                    );
                    if (\npdc\config::$reviewBeforePublish) {
                        $this->sendSubmitterMail($data, 'published');
                    } else {
                        $submitter = $this->model->getLastStatusChange(
                            \npdc\lib\Args::get('id'),
                            $data[$this->name.'_version'],
                            'published'
                        );
                        $mail = new \npdc\lib\Mailer();
                        $mail->to(
                            \npdc\config::$mail['contact'],
                            \npdc\config::$siteName
                        );
                        $mail->subject(ucfirst($this->name).' published');
                        $text = 'Dear admin'.",\r\n\r"
                            . 'The ' . $this->name . ' \'' . $data['title']
                            . '\' at ' . $_SERVER['REQUEST_SCHEME'] . '://'
                            . $_SERVER['HTTP_HOST'] . BASE_URL . '/'
                            . $this->name . '/' . \npdc\lib\Args::get('id')
                            . ' has been published by ' . $submitter['name']
                            . ".\r\n\r\nKind regards,\r\n" 
                            . \npdc\config::$siteName;

                        $mail->text($text);
                        $mail->send();
                    }
                }
                \npdc\view\Base::checkUnpublished();
                header(
                    'Location: ' . BASE_URL . '/' . $this->name . '/'
                    . \npdc\lib\Args::get('id')
                );
                die();
            case 'cancel':
                $data = $this->model->getById(
                    \npdc\lib\Args::get('id'),
                    'submitted'
                );
                if (!empty($data)) {
                    $this->model->setStatus(
                        \npdc\lib\Args::get('id'),
                        'submitted',
                        'draft',
                        $_GET['comment']
                    );
                    $_SESSION['notice'] = 'The record has been reset to draft';
                    $this->sendSubmitterMail($data, 'rejected');
                }
                header(
                    'Location: ' . BASE_URL . '/' . $this->name . '/'
                    . \npdc\lib\Args::get('id') . '/'
                    . $data[$his->name . '_version']
                );
                die();
            case 'new':
                $this->screen = 'general';
                $this->page();
                $this->display = 'edit_form';
                break;
            case 'edit':
            default:
                $data = $this->model->getById(
                    \npdc\lib\Args::get('id'),
                    'submitted'
                );
                if (
                    $data !== false
                    && $this->session->userLevel < NPDC_ADMIN
                ) {
                    $this->display = 'under_review';
                } else {
                    $this->id = \npdc\lib\Args::get('id');
                    $this->formId = $this->name.'_'.$this->id;
                    
                    $this->screen =
                        (
                            \npdc\lib\Args::get('action') === 'new' 
                            || empty(\npdc\lib\Args::get('subaction'))
                        )
                        ? 'general'
                        : \npdc\lib\Args::get('subaction');
                    
                    $this->page();
                    $this->display = 'edit_form';
            }
        }
    }
    
    /**
     * display of $_SESSION['warnings']
     *
     * @return string Formatted warnings
     */
    public function showWarnings() {
        $output = 'The following problems have been found in your record, please check and correct these.';
        global $session;
        if ($session->userLevel >= NPDC_ADMIN) {
            $output .= '<div id="adminoverrule">As admin you can publish a record that contains errors, please do so with care!'
                . '<form action="publish" method="get">'
                . '<input type="checkbox" id="adminoverruleinput" name="adminoverrule" value="adminoverrule">'
                . '<label for="adminoverruleinput"><span class="indicator"></span>I am aware of the risks and wish to use the admin override when publishing this page.</label>'
                . '<br/><input type="submit" value="Do publish" /></form>'
                . '</div>';
        }
        foreach ($_SESSION['warnings'] as $section=>$data) {
            if (!empty($data[0])) {
                $output .= '<h3><a href="' . BASE_URL . '/' . $this->name . '/'
                    . $this->id . '/edit/' . $section . '">' . $data[0]
                    . '</a></h3><ul>';
            }
            foreach ($data[1] as $warning) {
                $output .= '<li>'.$warning.'</li>';
            }
            $output .= '</ul>';
        }
        unset($_SESSION['warnings']);
        return $output;
    }
    
    /**
     * Send review result to person who submitted entry
     *
     * @param array $data The record that is reviewed
     * @param string $status New status of the record
     * @return void
     */
    private function sendSubmitterMail($data, $status) {
        $submitter = $this->model->getLastStatusChange(
            \npdc\lib\Args::get('id'),
            $data[$this->name.'_version'],
            'submitted'
        );
        $mail = new \npdc\lib\Mailer();
        $mail->to($submitter['mail'], $submitter['name']);
        $mail->subject(ucfirst($this->name) . ' ' . $status);
        $text = 'Dear ' . $submitter['name'] . ",\r\n\r"
            . 'Your ' . $this->name . ' \'' . $data['title'] . '\' has been '
            . $status . '. ';
        if ($status === 'published') {
            $text .= 'It\'s available at ' . $_SERVER['REQUEST_SCHEME'] . '://'
                . $_SERVER['HTTP_HOST'] . BASE_URL . '/' . $this->name . '/'
                . \npdc\lib\Args::get('id');
            if (!empty($_GET['comment'])) {
                $text .= "\r\n\r\nThe reviewer added the following comment:\r\n"
                    . $_GET['comment'];
            }
        } else {
            $text .= 'Please have a look at the comments below and edit your '
                . 'record at ' . $_SERVER['REQUEST_SCHEME'] . '://'
                . $_SERVER['HTTP_HOST'] . BASE_URL . '/' . $this->name
                . '/' . \npdc\lib\Args::get('id') . '/edit' . "\r\n\r\n"
                . $_GET['comment'];
        }
        $text .= "\r\n\r\nKind regards,\r\n" . \npdc\config::$siteName;
        $mail->text($text);
        $mail->send();
    }
    
    /**
     * Load and/or process form
     *
     * @return void
     */
    protected function page() {
        $this->formController = new \npdc\controller\Form($this->formId);
        $this->formController->getForm($this->name.'_'.$this->screen);
        $this->alterFields();
        $this->formController->form->action = $_SERVER['REQUEST_URI'];
        if (\npdc\lib\Args::get('action') === 'new') {
            $_SESSION[$this->formId]['db_action'] = 'insert';
            $this->version = 1;
        } else {
            $baseData = $this->model->getById($this->id, 'draft');
            $_SESSION[$this->formId]['db_action'] = 'update';
            if (empty($baseData) && $this->session->userLevel >= NPDC_ADMIN) {
                $baseData = $this->model->getById($this->id, 'submitted');
            }
            if (empty($baseData)) {
                $baseData = $this->model->getById($this->id);
            }
            $this->version = $baseData[$this->name.'_version'];
        }

        if (isset($_POST['formid'])) {
            $this->formController->doCheck();
            if ($this->formController->ok) {
                $this->id = $_POST[$this->name.'_id']
                    ?? \npdc\lib\Args::get('id')
                    ?? 'new';
                if (
                    $this->id !== 'new'
                    && $baseData['record_status'] === 'published'
                    && !(
                        $this->session->userLevel >= NPDC_OFFICER
                        && $_SESSION[$this->formId]['data']['rev'] === 'minor'
                    )
                ) {
                    $baseData[$this->name . '_version']++;
                    $baseData['record_status'] = 'draft';
                    $baseData['creator'] = $this->session->userId;
                    $this->model->insertGeneral($baseData);
                    $this->version = $baseData[$this->name.'_version'];
                    $_SESSION[$this->formId]['data']['record_status'] = 'draft';
                    $_SESSION[$this->formId]['data'][$this->name . '_version'] =
                        $baseData[$this->name . '_version'];
                    $_SESSION[$this->formId]['data']['creator'] = 
                        $this->session->userId;
                }
                $this->doSave();
            }
        } else {
            $this->loadForm($baseData);
        }
    }

    /**
     * Change fields, overwritten in child classes
     *
     * @return void
     */
    protected function alterFields() {}

    /**
     * Get list of people
     *
     * @param array $filter Filter for name
     * @return array list of people
     */
    public function getPersons($filter = null) {
        $model = new \npdc\model\Person();
            $options = [];
            foreach ($model->getList($filter) ?? [] as $person) {
                $options[$person['person_id']] = $person['name'];
            }
            return $options;
    }

     /**
      * Search for organization
      *
      * @param string $filter Filter for name
      * @return array list of organisations
      */
    public function getOrganizations($filter = null) {
        $model = new \npdc\model\Organization();
        $options = [];
        foreach ($model->getList($filter) ?? [] as $org) {
            $options[$org['organization_id']] = $org['organization_name'];
        }
        return $options;
    }
    
    /**
     * add programs from database to form
     * @param string $field The field in which the programs are stored 
     *                  (default: program)
     */

     /**
      * Search for programs
      *
      * @param string $filter Filter for name
      * @return array list of programs
      */
    public function getPrograms($filter = null) {
        $model = new \npdc\model\Program();
        $options = [];
        foreach ($model->getList($filter) ?? [] as $prog) {
            $options[$prog['program_id']] = $prog['name'];
        }
        return $options;
    }

    /**
     * Get countries
     *
     * @return array list of countries
     */
    public function getCountries($showContinent = true) {
        $model = new \npdc\model\Country();
        $options = [];
        if ($showContinent) {
            $continent = '';
            foreach ($model->getListByContinent() as $country) {
                if ($country['continent_id'] !== $continent) {
                    $options[$country['continent_name']] = [];
                    $continent = $country['continent_id'];
                }
                $options[$country['continent_name']][$country['country_id']] = 
                    $country['country_name'];
            }
        } else {
            foreach ($model->getList() as $country) {
            $options[$country['country_id']] = $country['country_name'];
        }
        
        }
        return $options;
    }

    /**
     * Check if draft of record differs from published version
     */
    protected function generalHasChanged($id) {
        $published = $this->model->getById($id);
        $draft = $this->model->getById($id, 'draft');
        $submitted = $this->model->getById($id, 'submitted');
        $changed = false;
        if (empty($draft) && empty($submitted)) {
            $changed = null;
        } elseif (empty($published)) {
            $changed = true;
        } else {
            $test = $draft !== false ? $draft : $submitted;
            foreach ($test as $key=>$val) {
                if (!in_array(
                    $key,
                    [
                        'record_status',
                        'insert_timestamp',
                        'published',
                        $this->name . '_version'
                    ]
                )) {
                    if ($val !== $published[$key]) {
                        $changed = true;
                    }
                }
            }
        }
        return $changed;
    }
    
    /**
     * check if data in a table changed between published version and draft 
     * version
     *
     * @param string $tbl table to check
     * @param string $id record id to check
     * @param string $version version number of draft
     * @return boolean Indicates if record is changed
     */
    protected function tblHasChanged($tbl, $id, $version) {
        $q = \npdc\lib\Db::getDSQLcon()
                ->table($tbl)
                ->where($this->name.'_id', $id);
        $q->where($q->orExpr()
            ->where($this->name.'_version_min', $version)
            ->where($this->name.'_version_max', $version-1)
        );
        return count($q->get()) > 0;
    }
}