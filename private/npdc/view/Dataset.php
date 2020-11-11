<?php

namespace npdc\view;

class Dataset extends Base{
    public $title;
    public $left;
    public $mid;
    public $right;
    public $json;
    public $class;
    public $accessLevel;
    protected $session;
    protected $controller;
    public $canEdit = false;
    public $baseUrl;
    public $versions;
    public $allowDuplicate = true;

    /**
     * Constructor
     *
     * @param object $session login information
     *
     * @param object $controller dataset controller
     */
    public function __construct($session, $controller) {
        $this->session = $session;
        $this->controller = $controller;
        $this->baseUrl = \npdc\lib\Args::getBaseUrl();
        $this->model = new \npdc\model\Dataset();
        parent::__construct();
    }

    /**
     * List status changes of record
     *
     * @return void
     */
    public function listStatusChanges() {
        $version = $this->version;
        if (
            \npdc\lib\Args::exists('action')
            && in_array(\npdc\lib\Args::get('action'), ['edit', 'submit'])
        ) {
            $version = $this->versions[0]['dataset_version'];
        } elseif (\npdc\lib\Args::exists('version')) {
            $version = \npdc\lib\Args::get('version');
        }
        return $this->doListStatusChanges(\npdc\lib\Args::get('id'), $version);
    }

    /**
     * Show list of datasets
     *
     * @return void
     */
    public function showList() {
        $this->left = parent::showFilters($this->controller->formId);
        $list = $this->model->getList(
            isset($_SESSION[$this->controller->formId]['data'])
                ? $_SESSION[$this->controller->formId]['data']
                : null
            ,
            true);
        switch (NPDC_OUTPUT) {
            case 'xml':
                $this->xml =  new \SimpleXMLElement(
                    '<?xml version="1.0" encoding="utf-8"?><datasets></datasets>'
                );
                $fields = [
                    'uuid',
                    'dataset_id',
                    'dataset_version',
                    'title',
                    'published',
                    'url'
                ];
                foreach ($list as $dataset) {
                    $em = $this->xml->addChild('dataset');
                    foreach ($fields as $field) {
                        switch ($field) {
                            case 'url':
                                $value = 'http' 
                                . (
                                    empty($_SERVER['HTTPS'])
                                    ? ''
                                    : 's'
                                )
                                . '://' . $_SERVER['HTTP_HOST'] . BASE_URL . '/'
                                . $dataset['uuid'] . '.xml';
                                break;
                            default:
                                $value = $dataset[$field];

                        }
                        $em->addChild(
                            $field,
                            str_replace(
                                '&',
                                '&amp;',
                                strip_tags(html_entity_decode($value))
                            )
                        );
                    }
                }
                header('Content-Type: application/xml');
                $dom = new \DOMDocument('1.0');
                $dom->preserveWhiteSpace = false;
                $dom->formatOutput = true;
                $dom->loadXML($this->xml->asXML());
                echo $dom->saveXML();
                die();
                break;
            default:
                $this->class = 'list dataset';
                $this->title = 'Datasets';
                $list = array_values($list);
                $n = count($list);
                $page = \npdc\lib\Args::get('page') ?? 1;
                $list = array_slice(
                    $list,
                    ($page - 1) * \npdc\config::$rowsPerPage,
                    min($page * \npdc\config::$rowsPerPage, $n)
                );
                $this->makePager($n, $page);
                $this->mid = $this->displayTable(
                    'dataset',
                    $list,
                    [
                        'title'=>'Title',
                        'date_start'=>'Start date',
                        'date_end'=>'End date'
                    ],
                    ['dataset', 'dataset_id'],
                    true,
                    true
                    );
                    $this->json = array_merge([
                        '@context' => ['@vocab'=>'http://schema.org/'],
                        '@type' => ['Service', 'ResearchProject'],
                        'legalName' => \npdc\config::$siteName,
                        'url' => $_SERVER['REQUEST_SCHEME'] . '://' 
                            . $_SERVER['HTTP_HOST'] . BASE_URL,
                        'category' => ['Polar science'],
                        'logo' => [
                            '@type' => 'ImageObject',
                            'url' => $_SERVER['REQUEST_SCHEME'] . '://'
                                . $_SERVER['HTTP_HOST'] . BASE_URL
                                . '/img/logo.png'
                        ],
                    ], \npdc\config::$providerSchemaOrg);
                    $this->extraHeader .= 
                        '<script id="schemaorg" type="application/ld+json">'
                        . json_encode(
                            $this->json,
                            JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE
                        )
                        . '</script>';
        }
    }

    /**
     * Show a dataset
     *
     * @param integer|string $dataset id of dataset or new
     * @return void
     */
    public function showItem($dataset) {
        if (\npdc\lib\Args::get('action') !== 'new') {
            $this->canEdit = 
                isset($this->session->userId) 
                && (
                    $this->session->userLevel === NPDC_ADMIN 
                    || $this->model->isEditor($dataset, $this->session->userId)
                );
            $this->versions = $this->model->getVersions($dataset);

            if (
                in_array(
                    \npdc\lib\Args::get('action'),
                    ['edit', 'submit', 'warnings']
                )
            ) {
                $v = $this->versions[0]['dataset_version'];
            } elseif (\npdc\lib\Args::exists('version')) {
                $v = \npdc\lib\Args::get('version');
            }
            $this->data = isset($v)
                ? $this->model->getById($dataset, $v)
                : $this->model->getById($dataset);

            if (
                !$this->canEdit
                && !in_array(
                    $this->data['record_status'],
                    ['published', 'archived']
                )
            ) {
                $this->data = false;
            }

            if ($this->data === false && $this->canEdit) {
                $this->data = $this->model->getById($dataset, 1);
            }
            $this->version = $this->data['dataset_version'];
        }

        if ($this->data === false && \npdc\lib\Args::get('action') !== 'new') {
            $this->showError();
        } elseif (NPDC_OUTPUT === 'xml') {//show as xml
            $this->showXml();
        } elseif (in_array(NPDC_OUTPUT, ['ris', 'bib'])) {
            $this->showCitation();
        } elseif (\npdc\lib\Args::get('action') === 'duplicate') {
            if (!$this->canEdit) {
                header(
                    'Location: ' . BASE_URL . '/' . \npdc\lib\Args::get('type')
                    . '/' . $this->data['uuid']);
            }
            $this->title = 'Confirm dataset duplication';
            $this->mid = parent::parseTemplate('dataset_duplication');
            return;
        } elseif (
            (
                !$this->canEdit
                || is_null($this->controller->display)
            )
            && \npdc\lib\Args::get('action') !== 'new'
        ) {//display dataset
            $this->showDataset();
            if (
                !\npdc\lib\Args::exists('uuid')
                || !\npdc\lib\Args::exists('uuidtype')
            ) {
                $this->showCanonical();
            }
        } elseif (\npdc\lib\Args::get('action') === 'warnings') {
            $this->title = 'Please check - '.$this->data['title'];
            $this->mid = $this->controller->showWarnings();
        } else {
            $this->title = \npdc\lib\Args::get('action') === 'new'
                ? 'New dataset'
                : ('Edit dataset - ' . $this->data['title']);
            $this->baseUrl .= '/'.$this->versions[0]['dataset_version'];
            $this->loadEditPage($this->controller->pages);
        }
    }

    /**
     * Display data center details
     *
     * @param integer $organization_id organization id
     * @return array organzition details
     */
    private function displayDataCenter($organization_id) {
        $organization = $this->organizationModel->getById($organization_id);
        $return = [
            'Organization_Type'=>'DISTRIBUTOR',
            'Organization_Name'=>[
                'Short_Name'=>$organization['dif_code'],
                'Long_Name'=>$organization['organization_name']
            ],
            'Organization_URL'=>$organization['website']
        ];
        return $return;
    }

    /**
     * Display data center personnel details
     *
     * @param integer $person_id person id
     * @return array person details
     */
    private function displayDataCenterPersonnel($person_id) {
        $person = $this->personModel->getById($person_id);
        $return = [
            'First_Name'=>$person['given_name'] ?? $person['initials'],
            'Last_Name'=>$person['surname']
        ];
        $organizationDetail = $this->organizationModel->getById(
            $person['organization_id']
        );
        foreach ([
            'organization_address' => 'Street_Address',
            'organization_city' => 'City',
            'organization_zip' => 'Postal_Code',
            'country_name' => 'Country'
        ] as $source=>$target) {
            $return['Address'][$target] = $organizationDetail[$source];
        }

        foreach (
            [
                'personal' => 'Direct Line',
                'secretariat' => 'Telephone',
                'mobile' => 'Mobile'
            ]
            as $phoneType=>$label
        ) {
            if (
                $person['phone_' . $phoneType . '_public'] === 'yes'
                && !empty($person['phone_' . $phoneType])
            ) {
                $return['Phone'][] = [
                    'Number' => str_replace(
                        ['(0)', ' '],
                        '',
                        $person['phone_' . $phoneType]
                    ),
                    'Type'=>$label
                ];
            }
        }
        $return['Email'] = $person['mail'];
        return $return;
    }

    /**
     * Show dataset as xml
     *
     * @return void
     */
    private function showXml() {
        $this->personModel = new \npdc\model\Person();
        $this->organizationModel = new \npdc\model\Organization();
        include('template/dataset/dif.php');
        die();
    }

    private function makeJson() {
        $content = ['@type' => 'Dataset',
            'name' => $this->data['title'],
            'description' => strip_tags($this->data['summary']),
            'version' => $this->data['dataset_version'],
            'identifier' => 'urn:uuid:' . $this->data['uuid'],
            'url' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']
                . BASE_URL . '/dataset/' . $this->data['uuid'],
            'includedInDataCatalog' => [
                '@type' => 'DataCatalog',
                'name' => \npdc\config::$siteName,
                'url' => $_SERVER['REQUEST_SCHEME'] . '://'
                    . $_SERVER['HTTP_HOST'] . BASE_URL,
            ],
            'citation' => strip_tags(
                $this->model->getCitationString($this->data)
            )
        ];
        
        $content['keywords'] = [];
        foreach (
            $this->model->getTopics(
                $this->data['dataset_id'],
                $this->data['dataset_version']
            )
            as $i=>$topic
        ) {
            $cut = ':';
            $content['keywords'][] = 
                strpos($topic['description'], $cut) === false
                ? $topic['description']
                : trim(substr(
                    $topic['description'],
                    0,
                    strpos($topic['description'], $cut)
                ));
        }
        foreach (
            $this->model->getKeywords(
                $this->data['dataset_id'],
                $this->data['dataset_version']
            )
            as $i=>$keyword
        ) {
            $content['keywords'][] = $this->vocab->formatTerm(
                'vocab_science_keyword',
                $keyword
            );
        }
        foreach (
            $this->model->getAncillaryKeywords(
                $this->data['dataset_id'],
                $this->data['dataset_version']
            )
            as $word
        ) {
            $content['keywords'][] = $word['keyword'];
        }

        if (!empty($this->data['dif_id'])) {
            $content['sameAs'] = 'https://gcmd.nasa.gov/r/d/'
                . $this->data['dif_id'];
        }
        $this->json = [
            '@context' => ['@vocab' => 'http://schema.org/'],
            '@graph' => [$content]
        ];
    }

    /**
     * Output formatted citation
     *
     * Based on url extension either bib or ris format is provided
     *
     * @return void
     */
    private function showCitation() {
        $citation = $this->model->getCitations(
            $this->data['dataset_id'],
            $this->data['dataset_version'],
            'this'
        )[0];
        $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']
            . BASE_URL . '/dataset/' . $this->data['uuid'];

        $authors = explode(
            '; ',
            str_replace(
                ' &amp;',
                ';',
                (
                    $citation['creator']
                    ?? $this->model->getAuthors(
                        $this->data['dataset_id'],
                        $this->data['dataset_version']
                    )
                )
            )
        );
        foreach ($authors as $author) {
            list($last, $first) = explode(', ', $author);
            if (empty($str)) {
                $aut = $last;
            } else {
                $str .= ' and ';
            }
            $str .= str_replace(
                '  ',
                ' ',
                str_replace(
                    '.',
                    ' ',
                    $first
                )
                . ' {' . $last . '}');
        }

        $id = str_replace(
            ' ',
            '',
            $aut 
            . substr(
                (
                    $citation['release_date']
                    ?? $this->data['insert_timestamp']
                ),
                0,
                4
            )
            .substr(
                (
                    $citation['title']
                    ?? $this->data['title']
                ),
                0,
                5
            )
        );
        include('template/dataset/citation/' . NPDC_OUTPUT . '.php');
        header('Content-type: ' . $content_type);
        header(
            'Content-Disposition: attachment; filename="' . $this->data['uuid']
            . '.' . NPDC_OUTPUT . '"'
        );
        echo strip_tags($output);
        die();
    }

    /**
     * Display error when trying to display a dataset
     *
     * @return void
     */
    private function showError() {
        if (count($this->versions) === 0) {
            http_response_code(404);
            $this->title = 'Not found';
            $this->mid .= 'The requested dataset could not be found';
        } elseif (!$this->canEdit) {
            if ($this->session->userLevel === NPDC_PUBLIC) {
                $this->title = 'Please login';
                $this->mid .= 'Please login<script type="text/javascript" '
                    . 'language="javascript">$.ready(openOverlay(\'' . BASE_URL
                    . '/login?notice=login\'));</script>';
            } else {
                $this->title = 'No access';
                $this->mid .= 'No access';
            }
        } elseif (\npdc\lib\Args::exists('version')) {
            $this->title = 'No version ' . \npdc\lib\Args::get('version')
                . ' found';
            $this->mid .= 'There is no version '
                . \npdc\lib\Args::get('version') . ' of this dataset.';
        }
    }

    /**
     * Display a single dataset as html
     *
     * @return void
     */
    private function showDataset() {
        if ($this->canEdit && count($this->versions) > 1) {
            $v = $this->data['dataset_version'];
            $_SESSION['notice'] .= 'See version <select id="versionSelect" '
                . 'style="width:auto">';
            foreach ($this->versions as $version) {
                $_SESSION['notice'] .= '<option value="' . BASE_URL 
                    . '/dataset/' . $this->data['dataset_id'] . '/'
                    . $version['dataset_version'] . '" '
                    . (
                        in_array(
                            $v,
                            [
                                $version['dataset_version'],
                                $version['record_status']
                            ]
                        )
                        ? 'selected=true'
                        : ''
                    )
                    . '>' . $version['dataset_version'] . ' - '
                    . $version['record_status'] . '</option>';
            }
            $_SESSION['notice'] .= '</select>';
        }
        $changed = $this->controller->recordChanged(
            $this->data['dataset_id'],
            $this->data['dataset_version']
        );
        if (!$changed) {
            if ($this->data['record_status'] === 'draft') {
                $_SESSION['notice'] .= ' Publishing this draft is not possible '
                    . 'since it doesn\'t appear to be different than the '
                    . 'published record.';
            }
        } elseif (
            \npdc\lib\Args::get('action') === 'submit'
            && $this->data['record_status'] === 'draft'
        ) {
            $_SESSION['notice'] = $this->controller->submitForm;
        } elseif ($this->data['record_status'] !== 'published') {
            if (
                $this->session->userLevel === NPDC_ADMIN
                && $this->data['record_status'] === 'submitted'
            ) {
                if (\npdc\lib\Args::get('action') !== 'submitted') {
                    header(
                        'Location: ' . BASE_URL . '/dataset/'
                        . \npdc\lib\Args::get('id').'/submitted'
                    );
                    die();
                }
                $_SESSION['notice'] = $this->controller->publishForm;
            }
            $_SESSION['notice'] .= ' You are looking at an '
                . $this->data['record_status'] . ' version of this page'
                . (
                    $this->data['record_status'] === 'draft'
                    ? $this->controller->draftMsg
                    : ''
                );
            if (!$this->canEdit) {
                $cur = $this->model->getById($this->data['dataset_id']);
                $_SESSION['notice'] .= ', the current version can be found '
                    . '<a href="' . BASE_URL . '/dataset/' . $cur['uuid'] 
                    . '">here</a>';
            }
        }
        $this->title = 'Dataset - ' . $this->data['title']
            . (
                is_null($this->data['acronym'])
                ? ''
                : ' (' . $this->data['acronym'] . ')'
            );
        $this->class = 'detail';
        if (\npdc\lib\Args::get('action') === 'files') {
            if (\npdc\lib\Args::get('subaction') === 'request') {
                if ($this->session->userLevel === NPDC_PUBLIC) {
                    header(
                        'Location: ' . BASE_URL . '/dataset/'
                        . $this->data['uuid'] . '/files'
                    );
                    die();
                }
                $this->mid .= parent::parseTemplate('dataset_files_request_mid');
                $this->right = parent::parseTemplate('dataset_files_right');
            } else {
                $this->mid .= parent::parseTemplate('dataset_files_mid');
                $this->right = parent::parseTemplate('dataset_files_right');
            }
            $this->bottom = parent::parseTemplate('foot_technical');
        } else {
            $this->makeJson();
            $this->mid .= parent::parseTemplate('dataset_mid');
            $this->right = parent::parseTemplate('dataset_right');
            $this->bottom = parent::parseTemplate('foot_technical');
            if(NPDC_OUTPUT === 'json'){
                header('Content-type: Application/json');
                echo json_encode(
                    $this->json['@graph'][0],
                    JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE
                );
                die();
            } elseif (
                \npdc\lib\Args::exists('uuid')
                && \npdc\lib\Args::exists('uuidtype')
            ) {
                $this->extraHeader .= '<script id="schemaorg" type="application'
                    . '/ld+json">'
                    . json_encode(
                        $this->json,
                        JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE
                    )
                    . '</script>';
            }
        }
    }

    /**
     * Display platform, instrument or sensor characteristics
     *
     * @param string $type record type (platform, instrument or sensor)
     * @param integer $id record id ($type_id)
     * @param integer $version record version
     * @return void
     */
    public function showCharacteristics($type, $id, $version) {
        $characteristics = $this->model->getCharacteristics(
            $type,
            $id,
            $version
        );
        if (count($characteristics) > 0) {
            echo '<h4>Characteristics</h4><table style="width:auto">';
            foreach ($characteristics as $characteristic) {
                echo '<tr><td>' . $characteristic['name'] . ':</td><td>'
                    . $characteristic['value'] . ' '
                    . $characteristic['unit'] . '</td></tr>';
            }
            echo '</table>';
        }
    }
}