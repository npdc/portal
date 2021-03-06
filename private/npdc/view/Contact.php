<?php

/**
 * global contact page and personal contact forms view
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\view;

class Contact extends Base{
    public $title = 'Contact';
    public $mid;
    public $right;
    public $class = 'contact';
    public $takeover = false;

    protected $session;
    protected $controller;
    protected $userLevelAdd = NPDC_OFFICER;
    
    /**
     * Constructor
     *
     * @param object $session login information
     *
     * @param object $controller contact controller
     */
    public function __construct($session, $controller) {
        $this->session = $session;
        $this->controller = $controller;
        $this->baseUrl = 'person/'.\npdc\lib\Args::get('id');
    }
    
    /**
     * display contact form
     * 
     * @param string $title form title
     * @param string $head text above form
     * 
     * @return void
     */
    private function showForm($title, $head) {
        if (
            isset($_SESSION[$this->controller->formId]['state'])
            && $_SESSION[$this->controller->formId]['state'] === 'sent'
        ) {
            $this->mid = 'Thank you for your message. A copy has just been sent to your mailbox.';
            unset($_SESSION[$this->controller->formId]);
        } else {
            $formView = new \npdc\view\Form($this->controller->formId);
            $this->mid = '<h4>' . $title . '</h4><p>' . $head . '</p>'
                . $formView->create($this->controller->form);
        }
    }
    
    /**
     * the general contact form
     * 
     * @return void
     */
    public function showList() {
        $this->showForm(
            'Send us a message',
            'Through this form you can send a message to our general mailbox. '
                . 'You can also send mails to individual people by clicking on '
                . 'their names.'
        );
        $controller = new \npdc\controller\Page($this->session);
        $pageView = new \npdc\view\Page($this->session, $controller);
        $pageView->showItem('npdc');
        $this->right = $pageView->right;
    }

    /**
     * a contact form for a specific person
     * Person details are in controller
     * 
     * @param integer $id Person id (not used, taken from controller)
     * @return void
     */
    public function showItem($id) {
        if (empty($this->controller->person)) {
            $this->mid = 'Person not found';
        } else {
            $this->takeover = $this->session->userLevel === NPDC_ADMIN;
            $personView = new \npdc\lib\Person();
            if (empty($this->controller->person['mail'])) {
                $this->mid = 'No mail address available for '
                    . $this->controller->person['name'];
            } else {
                $this->showForm(
                    'Send a message',
                    'Through this form you can send a message to '
                        . $this->controller->person['name']
                );
            }
            $this->right = 
                $personView->showPerson($this->controller->person, false)
                . $personView->showAddress($this->controller->person)
                . (
                    empty($this->controller->person['orcid'])
                    ? ''
                    : '<section class="inline"><h4>ORCID</h4><p><a href="https://orcid.org/'
                        . $this->controller->person['orcid'] . '">'
                        . $this->controller->person['orcid'] . '</a></p></section>'
                );
            
            $projects = $this->controller->model->getProjects(
                $this->controller->person['person_id']
            );
            $publications = $this->controller->model->getPublications(
                $this->controller->person['person_id']
            );
            $datasets = $this->controller->model->getDatasets(
                $this->controller->person['person_id']
            );
            $this->title = 'Contact - ' . $this->controller->person['name'];
            if (count($projects) + count($publications) + count($datasets) > 0) {
                $this->class .= ' single-col';
                $this->form = '<div class="contactwrapper"><h3>Contact form</h3>'
                    . '<div class="contactform">' . $this->mid . '</div></div>';
                $this->right .= $this->form;
                $this->mid = '';
                if (count($datasets) > 0) {
                    $this->mid .= '<h3>Datasets</h3>';
                    $this->mid .= $this->displayTable(
                        'dataset',
                        $datasets,
                        [
                            'title'=>'Title',
                            'date_start'=>'Start date',
                            'date_end'=>'End date'
                        ],
                        ['dataset', 'dataset_id']
                    );
                }
                if (count($publications) > 0) {
                    $this->mid .= '<h3>Publications</h3>';
                    $publicationModel = new \npdc\model\Publication();
                    foreach ($publications as $publication) {
                        $this->mid .= $publicationModel->getCitation(
                            $publication
                        );
                    }
                }
                if (count($projects) > 0) {
                    $this->mid .= '<h3>Projects</h3>';
                    $this->mid .= $this->displayTable(
                        'project',
                        $projects,
                        [
                            'title'=>'Title',
                            'nwo_project_id'=>'Funding id',
                            'date_start'=>'Start date',
                            'date_end'=>'End date'
                        ],
                        ['project', 'project_id']
                    );
                }
            }
            if ($this->session->userLevel >= NPDC_OFFICER) {
                $this->canEdit = true;
            }
        }
    }
}
