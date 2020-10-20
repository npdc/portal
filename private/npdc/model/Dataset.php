<?php

/**
 * Dataset model
 *
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\model;

class Dataset extends Base{
    protected $baseTbl = 'dataset';

    /**
     * GETTERS
     */

    /**
     * Get list of datasets
     *
     * @param array|null $filters (Optional) filters to filter datasets by
     * @return array List of datasets
     */
    public function getList($filters=null) {
        global $session;
        $q = $this->dsql->dsql()->table('dataset');
        if (!is_null($filters)) {
            foreach ($filters as $filter=>$values) {
                if (empty($values)) {
                    continue;
                }
                switch ($filter) {
                    case 'region':
                        $q->where('region', $values);
                        break;
                    case 'period':
                        // use values swapped, include all records with start
                        // date before end date of filter and end date after
                        // start date of filter
                        if (!empty($values[1])) {
                            $q->where('date_start', '<=', $values[1]);
                        }
                        if (!empty($values[0])) {
                            $q->where('date_end', '>=', $values[0]);
                        }
                        break;
                    case 'organization':
                        $q->where(
                            $q->dsql()->orExpr()
                            ->where('originating_center', $values)
                            ->where('dataset_id',
                            $q->dsql()->table('dataset_person')
                                ->field('dataset_id')
                                ->where(\npdc\lib\Db::joinVersion(
                                    'dataset',
                                    'dataset_person'
                                ))
                                ->where('organization_id', $values)
                            )
                        );
                        break;
                    case 'getData':
                        $gd = $q->dsql()->orExpr();
                        if (in_array('direct', $values)) {
                            $gd->where('dataset_id',
                            $q->dsql()->table('dataset_file')
                            ->field('dataset_id')
                            ->join('file.file_id', 'file_id', 'inner')
                            ->where('default_access', '<>', 'hidden')
                            ->where(\npdc\lib\Db::joinVersion(
                                'dataset',
                                'dataset_file'
                            ))
                            );
                        }
                        if (in_array('external', $values)) {
                            $gd->where('dataset_id',
                                $q->dsql()->table('dataset_link')
                                ->field('dataset_id')
                                ->join(
                                    'vocab_url_type.vocab_url_type_id',
                                    'vocab_url_type_id',
                                    'inner'
                                )
                                ->where('type', 'GET DATA')
                                ->where(\npdc\lib\Db::joinVersion(
                                    'dataset',
                                    'dataset_link'
                                ))
                            );
                        }
                        $q->where($gd);
                }
            }
        }
        $q->order(
                '(CASE WHEN date_start IS NULL THEN 0 ELSE 1 END),'
                . ' date_start DESC,'
                . ' date_end DESC'
            )
            ->field('dataset.*')
            ->field($q->dsql()->expr("'Dataset'"), 'content_type')
            ->field($q->dsql()
                ->expr(
                    'CASE WHEN record_status = [] THEN TRUE ELSE FALSE END {}',
                    ['draft', 'hasDraft']
                )
            );
        if ($session->userLevel > NPDC_USER) {
            if ($session->userLevel === NPDC_ADMIN) {
                $q->field(
                        $q->dsql()->expr('TRUE {}', ['editor'])
                    )
                    ->where(
                        'dataset.dataset_version',
                        $q->dsql()->table(['ds2'=>'dataset'])
                            ->field('MAX(dataset_version)')
                            ->where('ds2.dataset_id=dataset.dataset_id')
                    );
            } elseif ($session->userLevel === NPDC_EDITOR) {
                $isEditor = $q->dsql()->table('dataset_person')
                    ->field('dataset_id')
                    ->where(\npdc\lib\Db::joinVersion(
                        'dataset',
                        'dataset_person'
                    ))
                    ->where('person_id', $session->userId)
                    ->where('editor');
                $q->field(
                    $q->dsql()
                        ->expr(
                            'CASE
                                WHEN creator=[] THEN TRUE
                                WHEN EXISTS([]) THEN TRUE
                                ELSE FALSE
                                END {}',
                            [$session->userId, $isEditor, 'editor']
                        )
                    )
                    ->where(
                        'dataset.dataset_version',
                        $q->dsql()->table(['ds2'=>'dataset'])
                            ->field('MAX(dataset_version)')
                            ->where('ds2.dataset_id=dataset.dataset_id')
                            ->where($q->dsql()->andExpr()
                                ->where('record_status', 'draft')
                                ->where($q->dsql()
                                    ->expr(
                                        'CASE
                                        WHEN creator=[] THEN FALSE
                                        WHEN EXISTS([]) THEN FALSE
                                        ELSE TRUE
                                        END',
                                        [$session->userId, $isEditor]
                                    )
                                )
                                , false)// inverts condition to: NOT (draft & NOT editor)
                                
                );
            } else {
                $q->field($q->dsql()->expr('FALSE {}', ['editor']));
            }
            switch ($filters['editorOptions'][0]) {
                case 'all':
                    break;
                case 'unpublished':
                    $q->where('dataset_version', 1);
                case 'draft':
                    $q->where('record_status', 'draft');
                case 'edit':
                    if ($session->userLevel === NPDC_EDITOR) {
                        $q->where(
                            $q->dsql()->orExpr()
                                ->where('creator', $session->userId)
                                ->where(
                                    $q->dsql()
                                        ->expr('EXISTS([])', [$isEditor])
                                )
                        );
                    }
                    break;
            }
        } else {
            $q->where('record_status', 'published');
        }
        return $q->get();
    }

    /**
     * Get dataset by id
     *
     * @param intger $id dataset id
     * @param integer|string $version either numeric version, or record status
     * @return array a dataset
     */
    public function getById($id, $version = 'published') {
        return \npdc\lib\Db::get(
            'dataset',
            [
                'dataset_id' => $id,
                (
                    is_numeric($version)
                    ? 'dataset_version'
                    : 'record_status'
                ) => $version
            ]
        );
    }

    /**
     * Get dataset by uuid
     *
     * @param string $uuid the uuid
     * @return array a dataset
     */
    public function getByUUID($uuid) {
        return \npdc\lib\Db::get('dataset', ['uuid' => $uuid]);
    }

    /**
     * Get the publications linked to a dataset
     *
     * @param integer $id dataset id
     * @param integer $version version number of dataset
     * @param boolean $published only show published publications or also drafts
     * @return array list of publications
     */
    public function getPublications($id, $version, $published = true) {
    $q = $this->dsql->dsql()
        ->table('dataset_publication')
        ->join(
            'publication',
            \npdc\lib\Db::joinVersion('publication', 'dataset_publication'),
            'inner'
        )
        ->where(\npdc\lib\Db::selectVersion('dataset', $id, $version));
    $q->order(
        $q->expr(
            'date DESC, publication.publication_id, '
            . \npdc\lib\Db::$sortByRecordStatus
        )
    );
    if ($published) {
        $q->where('record_status', 'published');
    } else {
        $q->where('publication_version',
            $q->dsql()
            ->table('publication', 'a')
            ->field('max(publication_version)')
            ->where('a.publication_id=publication.publication_id')
            );
        }
        return $q->get();
    }

    /**
     * Get the projects linked to a dataset
     *
     * @param integer $id dataset id
     * @param integer $version version number of dataset
     * @param boolean $published only show published projects or also drafts
     * @return array list of projects
     */

    public function getProjects($id, $version, $published = true) {
        $q = $this->dsql->dsql()
            ->table('dataset_project')
            ->join(
                'project',
                \npdc\lib\Db::joinVersion('project', 'dataset_project'),
                'inner'
            )
            ->where(\npdc\lib\Db::selectVersion('dataset', $id, $version));
        $q->field('*')
            ->field($q->expr('date_start || \' - \' || date_end period'))
            ->order($q->expr(
                'date_start DESC, project.project_id, '
                . \npdc\lib\Db::$sortByRecordStatus
            )
        );
        if ($published) {
            $q->where('record_status', 'published');
        } else {
            $q->where('project_version',
                $q->dsql()
                    ->table('project', 'a')
                    ->field('MAX(project_version)')
                    ->where('a.project_id=project.project_id')
            );
        }
        return $q->get();
    }

    /**
     * Get the locations of data collection
     *
     * @param integer $id dataset id
     * @param integer $version dataset version
     * @return array list of locations
     */
    public function getLocations($id, $version) {
        return $this->dsql->dsql()
            ->table('location')
            ->join(
                'vocab_location.vocab_location_id',
                'vocab_location_id',
                'inner'
            )
            ->where(\npdc\lib\Db::selectVersion('dataset', $id, $version))
            ->get();
    }

    /**
     * Get the spatial coverages of a dataset
     *
     * @param integer $id dataset id
     * @param integer $version dataset version
     * @return array list of spatial coverages
     */
    public function getSpatialCoverages($id, $version) {
        //WKT and GEOM are kept in sync with a trigger
        return $this->dsql->dsql()
            ->table('spatial_coverage')
            ->where(\npdc\lib\Db::selectVersion('dataset', $id, $version))
            ->get();
    }

    /**
     * Get temporal coverages
     *
     * @param integer $id dataset id
     * @param integer $version dataset version
     * @return array list of temporal coverages
     */
    public function getTemporalCoverages($id, $version) {
        return $this->dsql->dsql()
            ->table('temporal_coverage')
            ->where(\npdc\lib\Db::selectVersion('dataset', $id, $version))
            ->get();
    }

    /**
     * Get temporal coverage subgroups
     *
     * @param string $group subgroup to retreive
     * @param integer $id temporal coverage id
     * @param integer $version dataset version
     * @return array groups of temporal coverage
     */
    public function getTemporalCoveragesGroup($group, $id, $version) {
        return $this->dsql->dsql()
            ->table('temporal_coverage_' . $group)
            ->where('temporal_coverage_id', $id)
            ->where(\npdc\lib\Db::selectVersion('dataset', $version))
            ->get();
    }

    /**
     * Get chronostraticgraphic units of a temporal coverage
     *
     * @param integer $id temporal coverage id
     * @param integer $version dataset version
     * @return array
     */
    public function getTemporalCoveragePaleoChronounit($id, $version) {
        return $this->dsql->dsql()
            ->table('temporal_coverage_paleo_chronounit')
            ->join(
                'vocab_chronounit.vocab_chronounit_id',
                'vocab_chronounit_id',
                'left'
            )
            ->where('temporal_coverage_paleo_id', $id)
            ->where(\npdc\lib\Db::selectVersion('dataset', $version))
            ->order('sort')
            ->get();
    }

    /**
     * Get data resolution
     *
     * @param integer $id dataset id
     * @param integer $version dataset version
     * @return void
     */
    public function getResolution($id, $version) {
        return $this->dsql->dsql()
            ->table('data_resolution')
                ->field('data_resolution.*')
            ->join('vocab_res_hor.vocab_res_hor_id', 'vocab_res_hor_id', 'left')
                ->field('vocab_res_hor.range','hor_range')
            ->join('vocab_res_vert.vocab_res_vert_id', 'vocab_res_vert_id', 'left')
                ->field('vocab_res_vert.range','vert_range')
            ->join('vocab_res_time.vocab_res_time_id', 'vocab_res_time_id', 'left')
                ->field('vocab_res_time.range','time_range')
            ->where(\npdc\lib\Db::selectVersion('dataset', $id, $version))
            ->get();
    }

    /**
     * Get platform used for data collection
     *
     * @param integer $id dataset id
     * @param integer $version dataset version
     * @param boolean $join include information from vocab_platform in the result
     * @return array list of platforms
     */
    public function getPlatform($id, $version, $join=true) {
        $q = $this->dsql->dsql()
            ->table('platform');
        if ($join) {
            $q->join(
                'vocab_platform.vocab_platform_id',
                'vocab_platform_id',
                'inner'
            );
        }
        return $q->where(\npdc\lib\Db::selectVersion('dataset', $id, $version))
            ->get();
    }

    /**
     * Get instruments used on platform
     *
     * @param integer $id platform id
     * @param integer $version dataset version
     * @param boolean $join include information from vocab_instrument in the result
     * @return array list of instruments
     */
    public function getInstrument($id, $version, $join = true) {
        $q = $this->dsql->dsql()
            ->table('instrument');
        if ($join) {
            $q->join(
                'vocab_instrument.vocab_instrument_id',
                'vocab_instrument_id',
                'inner'
            );
        }
        return $q->where('platform_id', $id)
            ->where(\npdc\lib\Db::selectVersion('dataset', $version))
            ->get();
    }

    /**
     * Get sensors used in instrument
     *
     * @param integer $id instrument id
     * @param integer $version dataset version
     * @param boolean $join include information from vocab_instrument in the result
     * @return array list of sensors
     */
    public function getSensor($id, $version, $join = true) {
        $q =$this->dsql->dsql()
            ->table('sensor');
        if ($join) {
            $q->join(
                'vocab_instrument.vocab_instrument_id',
                'vocab_instrument_id',
                'inner'
            );
        }
        return $q->where('instrument_id', $id)
            ->where(\npdc\lib\Db::selectVersion('dataset', $version))
            ->get();
    }

    /**
     * Get characteristics of platform, instrument or sensor
     *
     * @param string $type either platform, instrument or sensor
     * @param integer $id id of $type
     * @param integer $version dataset version
     * @return array list of characteristics
     */
    public function getCharacteristics($type, $id, $version) {
        return $this->dsql->dsql()
            ->table('characteristics')
            ->where($type.'_id', $id)
            ->where(\npdc\lib\Db::selectVersion('dataset', $version))
            ->get();
    }

    /**
     * Get persons linked to dataset
     *
     * @param integer $id dataset id
     * @param integer $version dataset version
     * @return array list of persons
     */
    public function getPersons($id, $version) {
        return $this->dsql->dsql()
            ->table('dataset_person')->field('dataset_person.*')
            ->join('person.person_id', 'person_id', 'inner')->field('name')
            ->join('organization.organization_id', 'organization_id', 'left')
                ->field('organization_name')
            ->where(\npdc\lib\Db::selectVersion('dataset', $id, $version))
            ->order('sort')
            ->get();
    }

    /**
     * Generate author string from linked persons
     *
     * @param integer $dataset_id dataset id
     * @param integer $dataset_version dataset version
     * @param integer $names number of names to be displayed before 'et al'
     * @return string formatted list of names
     */
    public function getAuthors($dataset_id, $dataset_version, $names=2) {
        $q = $this->dsql->dsql()
            ->table('dataset_person');
        $res = $q->join('person.person_id', 'person_id', 'left')
            ->field(
                $q->expr(
                    'COALESCE(surname || \', \' || COALESCE(initials, given_name), name)'
                ),
                'name'
            )
            ->where(\npdc\lib\Db::selectVersion(
                'dataset',
                $dataset_id,
                $dataset_version
            ))
            ->order('sort')
            ->get();
        $c = count($res);
        if ($c === 1) {
            $return = $res[0]['name'];
        } elseif ($c === 2) {
            $return = $res[0]['name'] . ' &amp; ' . $res[1]['name'];
        } else {
            $return = '';
            if (!is_numeric($names) || $names < 1 || is_nan($names)) {
                $names = 1;
            }
            for ($i=0;$i<min($c-1, $names);$i++) {
                $return .= ($i>0 ? '; ' : '') . $res[$i]['name'];
            }
            if ($c <= $names+1) {
                $return .= ' &amp; ' . $res[$i]['name'];
            } else {
                $return .= '; et al.';
            }
        }
        return $return;
    }
    
    /**
     * get a full citation string of the dataset
     * 
     * @param array $data a dataset record
     */
    public function getCitationString($data) {
        $citation = $this->getCitations(
            $data['dataset_id'],
            $data['dataset_version'],
            'this'
        )[0];
        $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']
            . BASE_URL . '/dataset/' . $data['uuid'];
        return (
                $citation['creator'] 
                ?? $this->getAuthors($data['dataset_id'], $data['dataset_version'])
            )
            . ' (' . substr(
                $citation['release_date']
                ?? $data['insert_timestamp'], 0, 4
            )
            . ').' . ' <em>' . $data['title'] . '.</em>'
            . ' (v'.$data['dataset_version'].')'
            . (
                !is_null($citation['release_place'])
                ? ' ' . $citation['release_place'] . '.'
                : ''
            )
            . (
                !is_null($citation['series_name'])
                ? ' Part of <i>' . $citation['series_name'] . '</i>.'
                : ''
            )
            . (
                !is_null($citation['editor']) 
                ? ' Edited by ' . $citation['editor'] . '.'
                : ''
            )
            . (
                !is_null($citation['publisher'])
                ? ' Published by ' . $citation['publisher'] . '.'
                : ''
            )
            . ' <a href="' . $url . '">' . $url . '</a>';
    }

    /**
     * get the data center holding the data
     *
     * @param integer $id dataset id
     * @param integer $version dataset version
     * @param boolean $join give full organization name (otherwise only id)
     * @return array list of organizations
     */
    public function getDataCenter($id, $version, $join = true) {
        $q = $this->dsql->dsql()
            ->table('dataset_data_center');
        if ($join) {
            $q->join('organization.organization_id', 'organization_id', 'inner');
        }
        return $q->where(\npdc\lib\Db::selectVersion('dataset', $id, $version))
            ->get();
    }

    /**
     * Get details of people at data center
     *
     * @param integer $id data center id
     * @param integer $version dataset version
     * @param boolean $join include full details of persons
     * @return array list of people
     */
    public function getDataCenterPerson($id, $version, $join = true) {
        $q =$this->dsql->dsql()
            ->table('dataset_data_center_person');
        if ($join) {
            $q->join('person.person_id', 'person_id', 'inner');
        }
        return $q->where('dataset_data_center_id', $id)
            ->where(\npdc\lib\Db::selectVersion('dataset', $version))
            ->get();
    }

    /**
     * Get citations linked to dataset
     *
     * @param integer $id dataset id
     * @param integer $version dataset version
     * @param string|null $type type of citation, either this or other
     * @return array list of citations
     */
    public function getCitations($id, $version, $type = null) {
        $q = $this->dsql->dsql()
            ->table('dataset_citation')
            ->where(\npdc\lib\Db::selectVersion('dataset', $id, $version));
        if (!is_null($type)) {
            $q->where('type', $type);
        }
        return $q->get();
    }

    /**
     * Get related datasets
     *
     * @param integer $id dataset id
     * @param integer $version dataset version
     * @return array list of related datasets
     */
    public function getRelatedDatasets($id, $version) {
        return $this->dsql->dsql()
            ->table('related_dataset')
            ->where(\npdc\lib\Db::selectVersion('dataset', $id, $version))
            ->get();
    }
    /**
     * Get science keywords
     *
     * @param integer $id dataset id
     * @param integer $version dataset version
     * @return array List of keywords
     */
    public function getKeywords($id, $version) {
        $q = $this->dsql->dsql()
            ->table('dataset_keyword')
            ->join(
                'vocab_science_keyword.vocab_science_keyword_id',
                'vocab_science_keyword_id',
                'inner'
            )
            ->where(\npdc\lib\Db::selectVersion('dataset', $id, $version));
        return $q->order(
            $q->expr(
                'category, coalesce(topic, \'0\'), coalesce(term, \'0\'), '
                . 'coalesce(var_lvl_1, \'0\'), coalesce(var_lvl_2, \'0\'), '
                . 'coalesce(var_lvl_3, \'0\'), coalesce(free_text, \'0\')'
                )
            )
            ->get();
    }

    /**
     * Get free keywords
     *
     * @param integer $id dataset id
     * @param integer $version dataset version
     * @return array List of keywords
     */
    public function getAncillaryKeywords($id, $version) {
        return $this->dsql->dsql()
            ->table('dataset_ancillary_keyword')
            ->where(\npdc\lib\Db::selectVersion('dataset', $id, $version))
            ->order('keyword')
            ->get();
    }

    /**
     * Get ISO topics
     *
     * @param integer $id dataset id
     * @param integer $version dataset version
     * @return array List of ISO topics
     */
    public function getTopics($id, $version) {
        return $this->dsql->dsql()
            ->table('dataset_topic')
            ->join(
                'vocab_iso_topic_category.vocab_iso_topic_category_id',
                'vocab_iso_topic_category_id',
                'inner'
            )
            ->where(\npdc\lib\Db::selectVersion('dataset', $id, $version))
            ->order('topic')
            ->get();
    }

    /**
     * Get links
     *
     * @param integer $id dataset id
     * @param integer $version dataset version
     * @param boolean $getData include get data links
     * @return array list of links
     */
    public function getLinks($id, $version, $getData = false) {
        $q =$this->dsql->dsql()
            ->table('dataset_link')
            ->join(
                'vocab_url_type.vocab_url_type_id',
                'vocab_url_type_id',
                'inner'
            )
            ->where(\npdc\lib\Db::selectVersion('dataset', $id, $version));
        if ($getData) {
            $q->where('dataset_link.vocab_url_type_id', 4);
        } else {
            $q->where('dataset_link.vocab_url_type_id', '<>', 4);
        }
        return $q->get();
    }

    /**
     * Get urls of links
     *
     * @param integer $id link id
     * @param integer $version dataset version
     * @return array list of urls
     */
    public function getLinkUrls($id, $version) {
        return $this->dsql->dsql()
            ->table('dataset_link_url')
            ->where('dataset_link_id', $id)
            ->where(\npdc\lib\Db::selectVersion('dataset', $version))
            ->get();
    }

    /**
     * Get files related to dataset
     *
     * @param integer $id dataset id
     * @param integer $version dataset version
     * @return array list of files
     */
    public function getFiles($id, $version) {
        return $this->dsql->dsql()
            ->table('dataset_file')
            ->join('file.file_id', 'file_id', 'inner')
            ->where(\npdc\lib\Db::selectVersion('dataset', $id, $version))
            ->get();
    }

    /**
     * Search for datasets
     *
     * @param string $string String to search for
     * @param boolean|null $summary search in summary
     * @param array|null $exclude list of dataset ids to ignore
     * @param boolean|null $includeDraft also search in drafts
     * @return array list of datasets matching the filters
     */
    public function search($string, $summary = false, $exclude = null, $includeDraft = false) {
        $q = $this->dsql->dsql()
            ->table('dataset')
            ->field('*');
        $q->field(
                $q->expr('dataset_id, date_start || \' - \' || date_end'),
                'date'
            )
            ->field($q->expr('\'Dataset\''), 'content_type')
            ->order('date DESC');
        if (!empty($string)) {
            $string = '%'.$string.'%';
            $operator = \npdc\lib\Db::getLike();
            $s = $q->orExpr()
                ->where('title', $operator, $string)
                ->where('dif_id', $operator, str_replace(' ', '_', $string));
            if ($summary) {
                $s->where('summary', $operator, $string);
            }
            $q->where($s);
        }
        if (is_array($exclude) && count($exclude) > 0) {
            $q->where('dataset_id','NOT', $exclude);
        }
        if ($includeDraft) {
            $q->where(
                'dataset_version',
                $q->dsql()->table('dataset', 'a')->field('max(dataset_version)')
                    ->where('a.dataset_id=dataset.dataset_id')
            );
        } else {
            $q->where('record_status', 'published');
        }
        return $q->get();
    }

    /**
     * Generate metadata plain text file for use in data zip
     *
     * @param integer $dataset_id dataset id
     * @return string plain text dataset description
     */
    public function generateMeta($dataset_id) {
        $data = $this->getById($dataset_id);
        $meta = '*' . $data['title'] . "*\r\n\r\n*Cite dataset as*\r\n\r\n";
        foreach (
            $this->getCitations($dataset_id, $data['dataset_version'], 'this')
            as $citation
        ) {
            $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']
                . BASE_URL . '/dataset/' . $data['uuid'];
            $meta .= $citation['creator']
                . ' (' . substr($citation['release_date'],0,4) . ').'
                . ' /' . ($citation['title'] ?? $data['title']) . './'
                . (
                    !is_null($citation['version'])
                    ? ' (' . $citation['version'] . ')'
                    : ''
                )
                . (
                    !is_null($citation['release_place'])
                    ? ' ' . $citation['release_place'] . '.'
                    : ''
                )
                . (
                    !is_null($citation['editor'])
                    ? ' Edited by ' . $citation['editor'] . '.'
                    : ''
                )
                . (
                    !is_null($citation['publisher'])
                    ? ' Published by ' . $citation['publisher'] . '.'
                    : ''
                )
                . ' ' . $url . "\r\n";
        }
        $meta .= "\r\n*Use Constraints*\r\n" . $data['use_constraints'] 
            . "\r\n\r\n*Quality*\r\n" . $data['quality'] 
            . "\r\n\r\n*Full metadata*\r\n" . getProtocol()
            . $_SERVER['HTTP_HOST'] . BASE_URL . '/dataset/' . $data['uuid'];
        return $meta;
    }

    /**
     * SETTERS
     */

    /**
     * make sure empty values are null before sending to database
     *
     * @param array $data data to be reformatted
     * @param string $action will record be inserted or updated
     * @return array reformatted data
     */
    protected function parseGeneral($data, $action) {
        $fields = [
            'dif_id',
            'title',
            'summary',
            'purpose',
            'region',
            'date_start',
            'date_end',
            'quality',
            'license',
            'access_constraints',
            'use_constraints',
            'dataset_progress',
            'originating_center',
            'dif_revision_history',
            'version_description',
            'product_level_id',
            'collection_data_type',
            'extended_metadata',
            'record_status',
            'creator'
            ,'duplicate_of'
        ];
        if ($action === 'insert') {
            array_push($fields, 'dataset_version');
            if (is_numeric($data['dataset_id'])) {
                array_push($fields, 'dataset_id');
            }
        }
        $values = [];
        foreach ($fields as $field) {
            if (
                in_array($field, ['record_status', 'creator'])
                && empty($data[$field])
            ) {
                continue;
            }
            if (array_key_exists($field, $data)) {
                $values[$field] = empty($data[$field]) ? null : $data[$field];
            }
        }
        return $values;
    }

    /**
     * Insert spatial coverage
     *
     * @param array $data the data to insert
     * @return void
     */
    public function insertSpatialCoverage($data) {
        return \npdc\lib\Db::insert('spatial_coverage', $data, true);
    }

    /**
     * Update spatial coverage
     *
     * @param int $record record id
     * @param array $data the data
     * @param int $version data set versopm
     * @return void
     */
    public function updateSpatialCoverage($record, $data, $version) {
        return $this->_updateSub('spatial_coverage', $record, $data, $version);
    }

    public function deleteSpatialCoverage($dataset_id, $version, $current) {
        $this->_deleteSub('spatial_coverage', $dataset_id, $version, $current);
    }

    public function insertResolution($data) {
        return \npdc\lib\Db::insert('data_resolution', $data, true);
    }

    public function updateResolution($record, $data, $version) {
        return $this->_updateSub('data_resolution', $record, $data, $version);
    }

    public function deleteResolution($dataset_id, $version, $current) {
        $this->_deleteSub('data_resolution', $dataset_id, $version, $current);
    }

    public function insertLocation($data) {
        return \npdc\lib\Db::insert('location', $data, true);
    }

    public function updateLocation($record, $data, $version) {
        return $this->_updateSub('location', $record, $data, $version);
    }

    public function deleteLocation($dataset_id, $version, $current) {
        $this->_deleteSub('location', $dataset_id, $version, $current);
    }

    public function insertTemporalCoverage($data) {
        return \npdc\lib\Db::insert('temporal_coverage', $data, true);
    }

    public function deleteTemporalCoverage($dataset_id, $version, $current) {
        $this->_deleteSub('temporal_coverage', $dataset_id, $version, $current);
    }


    public function insertTemporalCoveragePeriod($data) {
        return \npdc\lib\Db::insert('temporal_coverage_period', $data, true);
    }

    public function updateTemporalCoveragePeriod($record, $data, $version) {
        return $this->_updateSub(
            'temporal_coverage_period',
            $record,
            $data,
            $version,
            'parent'
        );
    }

    public function deleteTemporalCoveragePeriod(
        $temporal_coverage_id,
        $version,
        $current
    ) {
        $this->_deleteSub(
            'temporal_coverage_period',
            $temporal_coverage_id,
            $version,
            $current,
            'temporal_coverage'
        );
    }

    public function insertTemporalCoverageCycle($data) {
        return \npdc\lib\Db::insert('temporal_coverage_cycle', $data, true);
    }

    public function updateTemporalCoverageCycle($record, $data, $version) {
        return $this->_updateSub(
            'temporal_coverage_cycle',
            $record,
            $data,
            $version
        );
    }

    public function deleteTemporalCoverageCycle(
        $temporal_coverage_id,
        $version,
        $current
    ) {
        $this->_deleteSub(
            'temporal_coverage_cycle',
            $temporal_coverage_id,
            $version,
            $current,
            'temporal_coverage'
        );
    }

    public function insertTemporalCoverageAncillary($data) {
        return \npdc\lib\Db::insert('temporal_coverage_ancillary', $data, true);
    }

    public function updateTemporalCoverageAncillary($record, $data, $version) {
        return $this->_updateSub(
            'temporal_coverage_ancillary',
            $record,
            $data,
            $version
        );
    }

    public function deleteTemporalCoverageAncillary(
        $temporal_coverage_id,
        $version,
        $current
    ) {
        $this->_deleteSub(
            'temporal_coverage_ancillary',
            $temporal_coverage_id,
            $version,
            $current,
            'temporal_coverage'
        );
    }

    public function insertTemporalCoveragePaleo($data) {
        return \npdc\lib\Db::insert('temporal_coverage_paleo', $data, true);
    }

    public function updateTemporalCoveragePaleo($record, $data, $version) {
        return $this->_updateSub(
            'temporal_coverage_paleo',
            $record,
            $data,
            $version
        );
    }

    public function deleteTemporalCoveragePaleo(
        $temporal_coverage_id,
        $version,
        $current
    ) {
        $this->_deleteSub(
            'temporal_coverage_paleo',
            $temporal_coverage_id,
            $version,
            $current,
            'temporal_coverage'
        );
    }

    public function insertTemporalCoveragePaleoChronounit($data) {
        return \npdc\lib\Db::insert(
            'temporal_coverage_paleo_chronounit',
            $data,
            true
        );
    }

    public function deleteTemporalCoveragePaleoChronounit($id, $coverage, $version) {
        $q = $this->dsql->dsql()
            ->table('temporal_coverage_paleo_chronounit')
            ->where('temporal_coverage_paleo_id', $coverage)
            ->where('dataset_version_max IS NULL')
            ->where('vocab_chronounit_id', $id)
            ->set('dataset_version_max', $version)
            ->update();
    }

    public function insertTopic($topic_id, $dataset_id, $dataset_version) {
        return \npdc\lib\Db::insert(
            'dataset_topic',
            [
                'dataset_id'=>$dataset_id,
                'dataset_version_min'=>$dataset_version,
                'vocab_iso_topic_category_id'=>$topic_id
            ]
        );
    }

    public function deleteTopic($topic_id, $dataset_id, $dataset_version) {
    $this->dsql->dsql()
    ->table('dataset_topic')
    ->where('vocab_iso_topic_category_id', $topic_id)
    ->where('dataset_id', $dataset_id)
    ->where('dataset_version_max IS NULL')
    ->set('dataset_version_max', $dataset_version)
    ->update();
    }

    public function insertScienceKeyword($data) {
        return \npdc\lib\Db::insert('dataset_keyword', $data, true);
    }

    public function updateScienceKeyword($record, $data, $version) {
        return $this->_updateSub('dataset_keyword', $record, $data, $version);
    }

    public function deleteScienceKeyword($dataset_id, $version, $current) {
        $this->_deleteSub('dataset_keyword', $dataset_id, $version, $current);
    }

    public function insertAncillaryKeyword($word, $id, $version) {
        return \npdc\lib\Db::insert(
            'dataset_ancillary_keyword',
            [
                'dataset_id'=>$id,
                'dataset_version_min'=>$version,
                'keyword'=>$word
            ],
            true
        );
    }

    public function deleteAncillaryKeyword($word, $id, $version) {
        $this->dsql->dsql()
            ->table('dataset_ancillary_keyword')
            ->where('dataset_id', $id)
            ->where('keyword', $word)
            ->where('dataset_version_max IS NULL')
            ->set('dataset_version_max', $version)
            ->update();
    }

    public function insertDataCenter($data) {
        return \npdc\lib\Db::insert('dataset_data_center', $data, true);
    }

    public function updateDataCenter($record, $data, $version) {
        return $this->_updateSub('dataset_data_center', $record, $data, $version);
    }

    public function deleteDataCenter($dataset_id, $version, $currentDataCenters) {
        $this->_deleteSub(
            'dataset_data_center',
            $dataset_id,
            $version,
            $currentDataCenters
        );
    }

    public function insertDataCenterPerson($data) {
        return \npdc\lib\Db::insert('dataset_data_center_person', $data);
    }

    public function deleteDataCenterPerson($person_id, $dataCenterId, $version) {
        $q = $this->dsql->dsql()
            ->table('dataset_data_center_person')
            ->where('dataset_data_center_id', $dataCenterId)
            ->where('person_id', $person_id)
            ->where('dataset_version_max IS NULL')
            ->set('dataset_version_max', $version)
            ->update();
    }

    public function insertProject($data) {
        return \npdc\lib\Db::insert('dataset_project', $data);
    }

    public function deleteProject($dataset_id, $version, $currentProjects) {
    $q = $this->dsql->dsql()
            ->table('dataset_project')
            ->where('dataset_id', $dataset_id)
            ->where('dataset_version_max IS NULL');
        if (count($currentProjects) > 0) {
            $q->where('project_id', 'NOT', $currentProjects);
        }
        $q->set('dataset_version_max', $version)
            ->update();
        return true;
    }

    public function insertPublication($data) {
        return \npdc\lib\Db::insert('dataset_publication', $data);
    }

    public function deletePublication($dataset_id, $version, $currentPublications) {
        $q = $this->dsql->dsql()
            ->table('dataset_publication')
            ->where('dataset_id', $dataset_id)
            ->where('dataset_version_max IS NULL');
        if (count($currentPublications) > 0) {
            $q->where('publication_id', 'NOT', $currentPublications);
        }
        $q->set('dataset_version_max', $version)
            ->update();
        return true;
    }

    public function insertCitation($data) {
        return \npdc\lib\Db::insert('dataset_citation', $data, true);
    }

    public function updateCitation($record, $data, $version) {
        return $this->_updateSub('dataset_citation', $record, $data, $version);
    }

    public function deleteCitation(
        $dataset_id,
        $version,
        $currentCitations,
        $type = null
    ) {
        $q = $this->dsql->dsql()
            ->table('dataset_citation')
            ->where('dataset_id', $dataset_id)
            ->where('dataset_version_max IS NULL');
        if (count($currentCitations) > 0) {
            $q->where('dataset_citation_id', 'NOT', $currentCitations);
        }
        if (!is_null($type)) {
            $q->where('type', $type);
        }
        $q->set('dataset_version_max', $version)
            ->update();
    }

    public function insertRelatedDataset($data) {
        return \npdc\lib\Db::insert('related_dataset', $data, true);
    }

    public function updateRelatedDataset($record, $data, $version) {
        return $this->_updateSub('related_dataset', $record, $data, $version);
    }

    public function deleteRelatedDataset(
        $dataset_id,
        $version,
        $currentRelatedDatasets
    ) {
        $this->_deleteSub(
            'related_dataset',
            $dataset_id,
            $version,
            $currentRelatedDatasets
        );
    }

    public function insertPlatform($data) {
        return \npdc\lib\Db::insert('platform', $data, true);
    }

    public function updatePlatform($record, $data, $version) {
        $oldRecord = \npdc\lib\Db::get('platform', $record);
        $createNew = false;
        foreach ($data as $key=>$val) {
            if ($val != $oldRecord[$key]) {
                $createNew = true;
            }
        }
        if ($oldRecord['dataset_version_min'] === $version && $createNew) {
            $return = \npdc\lib\Db::update('platform', $record, $data);
        } elseif ($createNew) {
            $return = \npdc\lib\Db::update(
                'platform',
                $record,
                ['dataset_version_max'=>$version-1]
            );
            $instruments = $this->getInstrument(
                $oldRecord['platform_id'],
                $version,
                false
            );
            $return = $this->insertPlatform(
                array_merge(
                    $data,
                    [
                        'dataset_version_min'=>$version,
                        'dataset_id'=>$oldRecord['dataset_id']
                    ]
                )
            );
            foreach ($instruments as $instrument) {
                $sensors = $this->getSensor(
                    $instrument['instrument_id'],
                    $version,
                    false
                );
                $instrument['dataset_version_min'] = $version;
                $instrument['old_instrument_id'] = $instrument['instrument_id'];
                $instrument['platform_id'] = $return;
                unset($instrument['instrument_id']);
                $i = $this->insertInstrument($instrument);
                foreach ($sensors as $sensor) {
                    $sensor['dataset_version_min'] = $version;
                    $sensor['old_sensor_id'] = $sensor['sensor_id'];
                    $sensor['instrument_id'] = $i;
                    unset($sensor['sensor_id']);
                    $this->insertSensor($sensor);
                }
            }
        } else {
            $return = true;
        }
        return $return;
    }

    public function deletePlatform($dataset_id, $version, $currentPlatforms) {
        $this->_deleteSub(
            'platform',
            $dataset_id,
            $version,
            $currentPlatforms
        );
        return true;
    }

    public function insertInstrument($data) {
        return \npdc\lib\Db::insert('instrument', $data, true);
    }

    public function updateInstrument($record, $data, $version) {
        $oldRecord = \npdc\lib\Db::get('instrument', $record);
        $createNew = false;
        foreach ($data as $key=>$val) {
            if ($val != $oldRecord[$key]) {
                $createNew = true;
            }
        }
        if ($oldRecord['dataset_version_min'] === $version && $createNew) {
            $return = \npdc\lib\Db::update('instrument', $record, $data);
        } elseif ($createNew) {
            $return = \npdc\lib\Db::update(
                'instrument',
                $record,
                ['dataset_version_max'=>$version-1]
            );
            $sensors = $this->getSensor(
                $oldRecord['instrument_id'],
                $version,
                false
            );
            $return = $this->insertInstrument(
                array_merge($data, ['dataset_version_min'=>$version])
            );
            if (count($sensors) > 0) {
                foreach ($sensors as $sensor) {
                    $sensor['dataset_version_min'] = $version;
                    $sensor['old_sensor_id'] = $sensor['sensor_id'];
                    $sensor['instrument_id'] = $return;
                    unset($sensor['sensor_id']);
                    $this->insertSensor($sensor);
                }
            }
        } else {
            $return = true;
        }
        return $return;
    }

    public function deleteInstrument(
        $platform_id,
        $version,
        $currentInstruments
    ) {
        $this->_deleteSub(
            'instrument',
            $platform_id,
            $version,
            $currentInstruments,
            'platform'
        );
    }

    public function insertSensor($data) {
        return \npdc\lib\Db::insert('sensor', $data, true);
    }

    public function updateSensor($record, $data, $version) {
        return $this->_updateSub('sensor', $record, $data, $version);
    }

    public function deleteSensor($instrument_id, $version, $currentSensors) {
        $this->_deleteSub(
            'sensor',
            $instrument_id,
            $version,
            $currentSensors,
            'instrument'
        );
    }

    public function insertCharacteristics($data) {
        return \npdc\lib\Db::insert('characteristics', $data, true);
    }

    public function updateCharacteristics($record_id, $data, $dataset_version) {
        return $this->_updateSub(
            'characteristics',
            $record_id,
            $data,
            $dataset_version
        );
    }

    public function deleteCharacteristics(
        $record,
        $dataset_version,
        $currentCharacteristics
    ) {
        list($type, $record_id) = $record;
        $q = $this->dsql->dsql()
            ->table('characteristics')
            ->where($type.'_id', $record_id)
            ->where('dataset_version_max IS NULL');
        if (count($currentCharacteristics) > 0) {
            $q->where('characteristics_id', 'NOT', $currentCharacteristics);
        }
        $q->set('dataset_version_max', $dataset_version)
            ->update();
    }

    public function insertLink($data) {
        return \npdc\lib\Db::insert('dataset_link', $data, true);
    }

    public function updateLink($record, $data, $version) {
        $oldRecord = \npdc\lib\Db::get('dataset_link', $record);
        $createNew = false;
        foreach ($data as $key=>$val) {
            if ($val != $oldRecord[$key]) {
                $createNew = true;
            }
        }
        if ($oldRecord['dataset_version_min'] === $version && $createNew) {
            $return = \npdc\lib\Db::update('dataset_link', $record, $data);
        } elseif ($createNew) {
            $return = \npdc\lib\Db::update(
                'dataset_link',
                $record,
                ['dataset_version_max'=>$version-1]
            );
            $urls = $this->getLinkUrls($oldRecord['dataset_link_id'], $version);
            $return = $this->insertLink(
                array_merge(
                    $data,
                    [
                        'dataset_version_min'=>$version,
                        'dataset_id'=>$oldRecord['dataset_id']
                    ]
                )
            );
            if (count($urls) > 0) {
                foreach ($urls as $url) {
                    $url['dataset_version_min'] = $version;
                    $url['old_dataset_link_url_id'] = $url['dataset_link_url_id'];
                    $url['dataset_link_id'] = $return;
                    unset($url['dataset_link_url_id']);
                    $this->insertLinkUrl($url);
                }
            }
        } else {
            $return = true;
        }
        return $return;
    }

    public function deleteLink($dataset_id, $version, $currentLinks) {
        $this->_deleteSub('dataset_link', $dataset_id, $version, $currentLinks);
    }

    public function insertLinkUrl($data) {
        return \npdc\lib\Db::insert('dataset_link_url', $data, true);
    }

    public function updateLinkUrl($record, $data, $version) {
        return $this->_updateSub('dataset_link_url', $record, $data, $version);
    }

    public function deleteLinkUrl($link_id, $version, $currentLinkUrls) {
        $q = $this->dsql->dsql()
            ->table('dataset_link_url')
            ->where('dataset_link_id', $link_id)
            ->where('dataset_version_max IS NULL');
        if (count($currentLinkUrls) > 0) {
            foreach ($currentLinkUrls as $dataset_link_url) {
                if (!is_numeric($dataset_link_url)) {
                    die(
                        'Something went wrong! (e_deleteLinkUrl '
                        . $dataset_link_url . ')'
                    );
                }
            }
            $q->where('dataset_link_url_id', 'NOT', $currentLinkUrls);
            $q->where('old_dataset_link_url_id', 'NOT', $currentLinkUrls);
        }
        $q->set('dataset_version_max', $version)
            ->update();
        return true;
    }

    public function insertFile($data) {
        return \npdc\lib\Db::insert('dataset_file', $data, true);
    }

    public function deleteFile($dataset_id, $version, $current) {
        $this->_deleteSub('dataset_file', $dataset_id, $version, $current);
    }
}