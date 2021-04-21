<?php

/**
 * Project model
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\model;

class Project extends Base{
    protected $baseTbl = 'project';

    /**
     * GETTERS
     */

    /**
     * Get list of projects
     *
     * @param array|null $filters (Optional) filters to filter projects by
     * @return array List of projects
     */
    public function getList($filters=null) {
        global $session;
        $q = $this->dsql->dsql()->table('project');
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
                        //use values swapped, include all records with start 
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
                        $q->where('project_id',
                            $q->dsql()->table('project_person')
                                ->field('project_id')
                                ->where(
                                    \npdc\lib\Db::joinVersion(
                                        'project',
                                        'project_person'
                                    )
                                )
                                ->where('organization_id', $values)
                        );
                        break;

                    case 'program':
                        $q->where('program_id', $values);
                        break;
                    case 'search':
                        $idString = implode(
                            '[.]?',
                            preg_replace(
                                "/[^. \-0-9a-zA-Z]/",
                                " ",
                                str_split($values['string'])
                            )
                        );
                        $string = '%'.$values['string'].'%';
                        $operator = \npdc\lib\Db::getLike();
                        $s = $q->orExpr()
                            ->where('title', $operator, $string)
                            ->where('acronym', $operator, $string)
                            ->where('nwo_project_id', $operator, $idString);
                        if ($values['summary']) {
                            $s->where('summary', $operator, $string);
                        }
                        $q->where($s);
                        break;
                    case 'exclude':
                        $q->where('project_id', 'not', $values);
                    break;
                }
            }
        }
        $q->order('date_start DESC, date_end DESC, project_id')
            ->field('project.*')
            ->field($q->expr('date_start || \' - \' || date_end'), 'period')
            ->field($q->dsql()->expr("'Project'"), 'content_type')
            ->field($q->dsql()
                ->expr('CASE WHEN record_status = [] THEN TRUE ELSE FALSE END {}', ['draft', 'hasDraft'])
            );
        if ($session->userLevel > NPDC_USER) {
            if ($session->userLevel >= NPDC_OFFICER) {
                $q->field($q->dsql()->expr('TRUE {}', ['editor']))
                    ->where('project.project_version', 
                    $q->dsql()->table(['ds2'=>'project'])
                        ->field('MAX(project_version)')
                        ->where('ds2.project_id=project.project_id')
                );
            } elseif ($session->userLevel === NPDC_EDITOR) {
                $isEditor = $q->dsql()->table('project_person')
                    ->field('project_id')
                    ->where(\npdc\lib\Db::joinVersion('project', 'project_person'))
                    ->where('person_id', $session->userId)
                    ->where('editor');
                $q->field($q->dsql()
                    ->expr('CASE 
                        WHEN creator=[] THEN TRUE
                        WHEN EXISTS([]) THEN TRUE
                        ELSE FALSE 
                        END {}', [$session->userId, $isEditor, 'editor']
                        )
                    )->where('project.project_version', 
                        $q->dsql()->table(['ds2'=>'project'])
                            ->field('MAX(project_version)')
                            ->where('ds2.project_id=project.project_id')
                            ->where($q->dsql()->andExpr()//ends with false, so inverts condition to: NOT (draft & NOT editor)
                                ->where('record_status', 'draft')
                                ->where($q->dsql()
                                    ->expr('CASE 
                                        WHEN creator=[] THEN FALSE
                                        WHEN EXISTS([]) THEN FALSE
                                        ELSE TRUE 
                                        END', [$session->userId, $isEditor]
                                        )
                                    )
                            , false)
                    );
            } else {
                $q->field($q->dsql()->expr('FALSE {}', ['editor']));
            }
            switch ($filters['editorOptions'][0]) {
                case 'all':
                    break;
                case 'unpublished':
                    $q->where('project_version', 1);
                case 'draft':
                    $q->where('record_status', 'draft');
                case 'edit':
                    if ($session->userLevel === NPDC_EDITOR) {
                        $q->where(
                            $q->dsql()->orExpr()
                                ->where('creator', $session->userId)
                                ->where($q->dsql()->expr('EXISTS([])', [$isEditor]))
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
     * retrieve a project by its id
     * 
     * @param integer $id project id
     * @param integer|string $version either numeric version, or record status
     * @return array a project
     */
    public function getById($id, $version='published') {
        return \npdc\lib\Db::get('project', ['project_id'=>$id, (is_numeric($version) ? 'project_version' : 'record_status')=>$version]);
    }

    /**
     * Get project by uuid
     *
     * @param string $uuid the uuid
     * @return array a project
     */
    public function getByUUID($uuid) {
        return \npdc\lib\Db::get('project', ['uuid'=>$uuid]);
    }
    
    /**
     * Get parent project(s) of project
     *
     * @param integer $id child project id
     * @return array parent projects
     */
    public function getParents($id) {
        $q = $this->dsql->dsql()
            ->table('project_project')
            ->join('project.project_id', 'parent_project_id', 'inner')
            ->field('*');
        return $q->field($q->expr('date_start || \' - \' || date_end'), 'period')
            ->where('record_status', 'published')
            ->where('child_project_id', $id)
            ->get();
    }
    
    /**
     * Get child project(s) of projects
     *
     * @param integer $id parent project id
     * @return array child projects
     */
    public function getChildren($id) {
        $q = $this->dsql->dsql()
            ->table('project_project')
            ->join('project.project_id', 'child_project_id', 'inner')
            ->field('*');
        return $q->field($q->expr('date_start || \' - \' || date_end'), 'period')
            ->where('record_status', 'published')
            ->where('child_project_id', $id)
            ->get();
    }
    
    /**
     * get list of persons of project
     * 
     * @param string $id project id
     * @param string $version project version
     * @return array list of persons
     */
    public function getPersons($id, $version) {
        return $this->dsql->dsql()
            ->table('project_person')->field('project_person.*')
            ->join('person.person_id', 'person_id', 'inner')->field('name')
            ->join('organization.organization_id', 'organization_id', 'left')->field('organization_name')
            ->where(\npdc\lib\Db::selectVersion('project', $id, $version))
            ->order('sort')
            ->get();
    }
    
    /**
     * get list of datasets linked to project
     * 
     * @param integer $id project id
     * @param integer $version project version
     * @param boolean $published only show published datasets
     * @return array list of datasets
    */
    public function getDatasets($id, $version, $published = true) {
        $q = $this->dsql->dsql()
            ->table('dataset_project')
            ->join('dataset', \npdc\lib\Db::joinVersion('dataset', 'dataset_project'), 'inner')
            ->where(\npdc\lib\Db::selectVersion('project', $id, $version));
        $q->order($q->expr('date_start DESC, dataset.dataset_id, '.\npdc\lib\Db::$sortByRecordStatus));
        if ($published) {
            $q->where('record_status', 'published');
        } else {
            $q->where('dataset_version',
                $q->dsql()
                    ->table('dataset', 'a')
                    ->field('max(dataset_version)')
                    ->where('a.dataset_id=dataset.dataset_id')
                );
        }
        return $q->get();
    }
    
    /**
     * Get the publications linked to a project
     *
     * @param integer $id project id
     * @param integer $version version number of project
     * @param boolean $published only show published publications or also drafts
     * @return array list of publications
     */
    public function getPublications($id, $version, $published = true) {
        $q = $this->dsql->dsql()
            ->table('project_publication')
            ->join('publication', \npdc\lib\Db::joinVersion('publication', 'project_publication'), 'inner')
            ->where(\npdc\lib\Db::selectVersion('project', $id, $version));
        $q->order($q->expr('date DESC, publication.publication_id, '.\npdc\lib\Db::$sortByRecordStatus));
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
     * Get keywords of project
     *
     * @param integer $id project id
     * @param integer $version project version
     * @return array list of keywords
     */    
    public function getKeywords($id, $version) {
        return $this->dsql->dsql()
            ->table('project_keyword')
            ->where(\npdc\lib\Db::selectVersion('project', $id, $version))
            ->order('keyword')
            ->get();
    }
    
    /**
     * get urls linked to project
     * @param string $id project id
     * @return array array of urls
     */
    public function getLinks($id, $version) {
        return $this->dsql->dsql()
            ->table('project_link')
            ->where(\npdc\lib\Db::selectVersion('project', $id, $version))
            ->order('text')
            ->get();
    }
    
    public function getSecondaryThemes($id, $version){
        return $this->dsql->dsql()
            ->table('project_theme')
            ->where(\npdc\lib\Db::selectVersion('project', $id, $version))
            ->get();
    }
    
    /**
     * function for the search page
     * @param string $string
     * @param boolean|null $summary search in summary
     * @param array|null $exclude list of project ids to ignore
     * @param boolean|null $includeDraft also search in drafts
     * @return array projects matching $string
     */
    public function search($string, $summary = false, $exclude = null, $includeDraft = false) {
        //return $this->getList(['search'=>['string'=>$string, 'summary'=>$summary], 'exclude'=>$exclude]);
        $q = $this->dsql->dsql()
            ->table('project')
            ->field('*');
        $q->field($q->expr('project_id, date_start || \' - \' || date_end'), 'date')
            ->field($q->expr('\'Project\''), 'content_type')
            ->order('date DESC');
        if (!empty($string)) {
            $idString = implode('[.]?', preg_replace("/[^. \-0-9a-zA-Z]/", " ", str_split($string)));
            $string = '%'.$string.'%';
            $operator = \npdc\lib\Db::getLike();
            $s = $q->orExpr()
                ->where('title', $operator, $string)
                ->where('acronym', $operator, $string)
                ->where('nwo_project_id', $operator, $idString);
            if ($summary) {
                $s->where('summary', $operator, $string);
            }
            $q->where($s);
        }
        if (is_array($exclude) && count($exclude) > 0) {
            $q->where('project_id', 'NOT', $exclude);
        }
        if ($includeDraft) {
            $q->where(
                'project_version',
                $q->dsql()->table('project', 'a')
                    ->field('max(project_version)')
                    ->where('a.project_id=project.project_id')
            );
        } else {
            $q->where('record_status', 'published');
        }
        return $q->get();
    }

    /**
     * SETTERS
     */
    public function insertLink($data) {
        return \npdc\lib\Db::insert('project_link', $data, true);
    }
    
    public function updateLink($id, $data, $version) {
        return $this->_updateSub('project_link', $id, $data, $version);
    }
    
    public function deleteLink($project_id, $version, $keep) {
        $this->_deleteSub('project_link', $project_id, $version, $keep);
    }
    
    public function insertPublication($data) {
        return \npdc\lib\Db::insert('project_publication', $data);
    }

    public function insertSecondaryTheme($theme, $project_id, $version){
        return \npdc\lib\Db::insert('project_theme', [
            'npp_theme_id' => $theme,
            'project_id' => $project_id,
            'project_version_min' => $version
        ]);
    }
    
    public function deleteSecondaryTheme($theme, $project, $version){
        $this->dsql->dsql()
            ->table('project_theme')
            ->where('npp_theme_id', $theme)
            ->where('project_id', $project)
            ->where('project_version_max IS NULL')
            ->set('project_version_max', $version)
            ->update();
    }
    
    public function deletePublication(
        $project_id,
        $version,
        $currentPublications
    ) {
        $q = $this->dsql->dsql()
            ->table('project_publication')
            ->where('project_id', $project_id)
            ->where('project_version_max IS NULL');
        if (count($currentPublications) > 0) {
            $q->where('publication_id', 'NOT', $currentPublications);
        }
        $q->set('project_version_max', $version)
            ->update();
        return true;
    }
    
    public function insertDataset($data) {
        return \npdc\lib\Db::insert('dataset_project', $data);
    }

    public function deleteDataset($project_id, $version, $currentDatasets) {
        $q = $this->dsql->dsql()
            ->table('dataset_project')
            ->where('project_id', $project_id)
            ->where('project_version_max IS NULL');
        if (count($currentDatasets) > 0) {
            $q->where('dataset_id', 'NOT', $currentDatasets);
        }
        $q->set('project_version_max', $version)
            ->update();
        return true;
    }

    /**
     * 
     * @param array $data
     * @param string $action Either update or insert
     */
    protected function parseGeneral($data, $action) {
        $fields = [
            'nwo_project_id',
            'title',
            'acronym',
            'region',
            'summary',
            'program_id',
            'date_start',
            'date_end',
            'research_type',
            'science_field',
            'record_status',
            'creator',
            'npp_theme_id'
        ];
        if ($action === 'insert') {
            array_push($fields, 'project_version');
            if (is_numeric($data['project_id'])) {
                array_push($fields, 'project_id');
            }
        }
        $values = [];
        foreach ($fields as $field) {
            switch ($field) {
                case 'date_start':
                    $values[$field] = $data['period'][0] ?? $data['date_start'];
                    break;
                case 'date_end':
                    $values[$field] = $data['period'][1] ?? $data['date_end'];
                    break;
                case 'creator':
                case 'record_status':
                    if (empty($data[$field])) {
                        break;
                    }
                default:
                    $values[$field] = empty($data[$field])
                        ? null
                        : $data[$field];
            }
        }
        return $values;
    }
}
