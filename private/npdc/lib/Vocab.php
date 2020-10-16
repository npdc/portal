<?php

/**
 * Helper for working with vocabs
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\lib;

class Vocab {
    // parts between { and } are only used when displaying a row, not for list, 
    // so for lists these parts are stripped in getList, for single the brackets
    // are removed in formatTerm
    private $fields = [
        'vocab_chronounit' => [
            'eon',
            'era',
            'period',
            'epoch',
            'stage'
        ],
        'vocab_instrument' => [
            'category',
            'class',
            'type',
            'subtype',
            [
                'short_name',
                'long_name'
            ]
        ],
        'vocab_location' => [
            'location_category',
            'location_type',
            'location_subregion1',
            'location_subregion2',
            'location_subregion3'
        ],
        'vocab_platform' => [
            'category',
            'series_entity',
            [
                'short_name',
                'long_name'
                ]
            ],
        'vocab_res_hor' => [
            '{hor_}range'
        ],
        'vocab_res_vert' => [
            '{vert_}range'
        ],
        'vocab_res_time' => [
            '{time_}range'
        ],
        'vocab_science_keyword' => [
            'topic',
            'term',
            'var_lvl_1',
            'var_lvl_2',
            'var_lvl_3',
            'detailed_variable'
        ],
        'vocab_url_type' => [
            'type',
            'subtype'
        ],
        'vocab_iso_topic_category' => [
            'description'
        ],
        'mime_type' => [
            'label',
            'type'
        ]
    ];
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->model = new \npdc\model\Vocab();
    }
    
    /**
     * Get fields we use in a vocab
     *
     * @param string $vocab name of vocab
     * @return array fields in vocab
     */
    public static function getFields($vocab) {
        $x = new self();
        return $x->fields['vocab_'.$vocab];
    }
    
    /**
     * Get (filtered) list of terms in vocab
     *
     * @param string $vocab name of vocab
     * @param string|null $filter string to filter by
     * @return array list of terms matching filter
     */
    public function getList($vocab, $filter = null) {
        $data = $this->model->listTerms($vocab);
        $return = [];
        $fields = $this->fields[$vocab];
        if (substr($vocab, 0, 10) === 'vocab_res_') {
            $this->fields[$vocab] = preg_replace('/\{(.*)\}/', '', $fields);
        }
        foreach($data as $row) {
            $term = $this->formatTerm($vocab, $row, true, false);
            if (
                empty($filter)
                || strpos(strtolower($term), strtolower($filter)) !== false
            ) {
                if (strlen($term) > 0) {
                    $return[$row[$vocab.'_id']] = $term;
                }
            }
        }
        $this->fields[$vocab] = $fields;
        return $return;
    }
    
    /**
     * implode fields of term into single string
     *
     * @param string $vocab vocab where the term comes from
     * @param array $row array containing term from vocab
     * @param boolean $last show last field
     * @param boolean $shorten shorten the values of the fields in the output
     * @return string
     */
    public function formatTerm($vocab, $row, $last = true, $shorten=false) {
        $fields = str_replace(['{', '}'], '', $this->fields[$vocab]);
        $parts = [];
        for($i=0;$i<($last ? count($fields) : count($fields)-1);$i++) {
            //if field has both short and long term the short should be between
            // brackets, this tests this
            if (is_array($fields[$i])) {
                $part = '';
                //test if long term has value, if so, display
                if (!empty($row[$fields[$i][1]])) {
                    $part .= $row[$fields[$i][1]];
                }
                
                //test if short term has value
                if (!empty($row[$fields[$i][0]])) {
                    //test if long term has value, if not, display short without
                    // brackets
                    if (empty($row[$fields[$i][1]])) {
                        $part .= $row[$fields[$i][0]];
                    } else {
                        $part .= ' (' . $row[$fields[$i][0]] . ')';
                    }
                }
                if (!empty($part)) {
                    $parts[] = $part;
                }
            } else {
                //single term
                if (!empty($row[$fields[$i]])) {
                    $parts[] = $row[$fields[$i]];
                }
            }
        }
        foreach($parts as $i=>&$part) {
            if ($shorten && strlen($part) > 12 && $i < count($parts)-1) {
                $part = substr($part, 0, 10) . '...';
            }
            unset($part);
        }
        return ucwords(strtolower(implode(' > ', $parts)));
    }

    /**
     * get IDN nodes for location from database
     *
     * @param string $location_id
     * @return array
     */
    public function getIDNNodes($location_id) {
        return $this->model->getIDNNode($location_id);
    }
}