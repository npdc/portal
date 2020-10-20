<?php

/**
 * Vocab controller
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\controller;

class Vocab {
    private $model;
    private $curl;
    
    /**
     * Contstructor
     */
    public function __construct() {
        $this->model = new \npdc\model\Vocab();
        $this->curl = new \npdc\lib\CurlWrapper();
        $this->curl->httpauth(
            \npdc\config::$gcmd['user'],
            \npdc\config::$gcmd['pass']
        );
    }

    /**
     * Provide value or null
     *
     * @param string $value
     * @return mixed either string or null
     */
    private function setValue($value) {
        return empty($value) ? null : $value;
    }
    
    /**
     * Refersh all vocabs to know which are updated
     *
     * @return void
     */
    public function refreshList() {
        echo 'refresh list <br/>';
        if ($this->model->getVocab(1)['last_update_local'] < date('Y-m-d')) {
            $url = 'https://gcmd.earthdata.nasa.gov/kms/concept_schemes';
            $res = simplexml_load_string($this->curl->get($url));
            if ($this->curl->status()['http_code'] === 200) {
                foreach($res->scheme as $scheme) {
                    echo $scheme['name'] . ': ' . $scheme['updateDate'] . '<br/>';
                    $vocab = (string)$scheme['name'];
                    if ($this->model->getVocab($vocab) === false) {
                        $this->model->addVocab(
                            [
                                'vocab_id'=>(int)$scheme['id'],
                                'vocab_name'=>(string)$scheme['name'],
                                'last_update_date'=>(string)$scheme['updateDate']
                            ]
                        );
                    } else {
                        $this->model->updateVocab(
                            $vocab,
                            [
                                'vocab_id'=>(int)$scheme['id'],
                                'last_update_date'=>(string)$scheme['updateDate']
                            ]
                        );
                    }
                }
                $this->model->updateVocab(1, ['last_update_local'=>date('Ymd')]);
            }    
        }
    }
    
    /**
     * Loop trough vocabs that need updating
     *
     * @return void
     */
    public function loopVocabs() {
        $vocabs = $this->model->getUpdatable();
        foreach($vocabs as $vocab) {
            echo 'Starting with ' . $vocab['vocab_name'] . ' - '
                . $vocab['vocab_id'] . '<br/>';
            $url = 'https://gcmd.earthdata.nasa.gov/kms/concepts/concept_scheme/'
                . $vocab['vocab_name'] . '?format=csv';
            $csv = str_getcsv($this->curl->get($url), "\n");
            $comment_lines = 1;
            $keys = str_getcsv($csv[$comment_lines]);
            $data = array_slice($csv, $comment_lines + 1);
            foreach($data as $row) {
                if (count($keys) !== count(str_getcsv($row))) {
                    echo 'Skipping ' . $row . '<br/>';
                    continue;
                }
                $row = array_combine($keys, str_getcsv($row));
                $uuid = $row['UUID'];
                if (!empty($uuid)) {
                    $function = 'process'.ucfirst($vocab['vocab_name']);
                    if(method_exists($this, $function)){
                        list($tbl, $values) = call_user_func(
                            [$this, $function],
                            $row
                        );
                    } else {
                        echo $vocab['vocab_name'].' not implemented';
                        continue 2;
                    }
                    $rec = $this->model->getTermByUUID($tbl, $uuid);
                    if (empty($rec)) {
                        $values['uuid'] = $uuid;
                        $this->model->insertTerm($tbl, $values);
                    } else {
                        $this->model->updateTerm($tbl, $rec[$tbl.'_id'], $values);
                    }
                }
            }
            $this->model->updateVocab($vocab['vocab_id'], ['last_update_local'=>date('Y-m-d')]);
        }
    }
    
    private function processChronounits($row){
        return [
            'vocab_chronounit',
            [
                'eon' => $this->setValue($row['Eon']),
                'era' => $this->setValue($row['Era']),
                'period' => $this->setValue($row['Period']),
                'epoch' => $this->setValue($row['Epoch']),
                'stage' => $this->setValue($row['Stage'])
            ]
        ];
    }
    
    private function processLocations($row){
        return [
            'vocab_location',
            [
                'location_category' => $this->setValue($row['Location_Category']),
                'location_type' => $this->setValue($row['Location_Type']),
                'location_subregion1' => $this->setValue($row['Location_Subregion1']),
                'location_subregion2' => $this->setValue($row['Location_Subregion2']),
                'location_subregion3' => $this->setValue($row['Location_Subregion3'])
            ]
        ];
    }
    
    private function processPlatforms($row){
        return [
            'vocab_platform',
            [
                'category' => $this->setValue($row['Category']),
                'series_entity' => $this->setValue($row['Series_Entity']),
                'short_name' => $this->setValue($row['Short_Name']),
                'long_name' => $this->setValue($row['Long_Name'])
            ]
        ];
    }
    
    private function processInstruments($row){
        return ['vocab_instrument',
            [
                'category' => $this->setValue($row['Category']),
                'class' => $this->setValue($row['Class']),
                'type' => $this->setValue($row['Type']),
                'subtype' => $this->setValue($row['Subtype']),
                'short_name' => $this->setValue($row['Short_Name']),
                'long_name' => $this->setValue($row['Long_Name'])
            ]
        ];
    }

    private function processIdnnode($row){
        return [
            'vocab_idn_node',
            [
                'short_name' => $this->setValue($row['Short_Name']),
                'long_name' => $this->setValue($row['Long_Name'])
            ]
        ];
    }
    
    private function processRucontenttype($row){
        return [
            'vocab_url_type',
            [
                'type' => $this->setValue($row['Type']),
                'subtype' => $this->setValue($row['Subtype'])
            ]
        ];
    }
    
    private function processHorizontalresolutionrange($row){
        return [
            'vocab_res_hor',
            [
                'range' => $this->setValue($row['Horizontal_Resolution_Range'])
            ]
        ];
    }

    private function processVerticalresolutionrange($row){
        return [
            'vocab_res_vert',
            [
                'range' => $this->setValue($row['Vertical_Resolution_Range'])
            ]
        ];
    }

    private function processTemporalresolutionrange($row){
        return [
            'vocab_res_time',
            [
                'range' => $this->setValue($row['Temporal_Resolution_Range'])
            ]
        ];
    }

    private function processSciencekeywords($row){
        return [
            'vocab_science_keyword',
            [
                'category' => $this->setValue($row['Category']),
                'topic' => $this->setValue($row['Topic']),
                'term' => $this->setValue($row['Term']),
                'var_lvl_1' => $this->setValue($row['Variable_Level_1']),
                'var_lvl_2' => $this->setValue($row['Variable_Level_2']),
                'var_lvl_3' => $this->setValue($row['Variable_Level_3']),
                'detailed_variable' => $this->setValue($row['Detailed_Variable'])
            ]
        ];
    }
    
}