<?php

/**
 * Publication model
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\model;

class Publication extends Base{
	protected $baseTbl = 'publication';

	/**
	 * GETTERS
	 */

	/**
	 * Get list of publications
	 *
	 * @param array|null $filters (optional) list of filters to apply
	 * @return array list of publications
	 */
	public function getList($filters=null){
		global $session;
		$q = $this->dsql->dsql()->table('publication');
		if(!is_null($filters)){
			foreach($filters as $filter=>$values){
				if((is_array($values) && count($values) === 0) || empty($values)){
					continue;
				}
				switch($filter){
					case 'region':
						$q->where('region', $values);
						break;
						
					case 'year':
						//use values swapped, include all records with start date before end date of filter and end date after start date of filter
						if(!empty($values[1])){
							$q->where('date <= ?', $values[1]);
						}
						if(!empty($values[0])){
							$q->where('date >= ?', $values[0]);
						}
						break;
					case 'organization':
						$q->where(
							$q->dsql()->orExpr()
								->where('publication_id',
									$q->dsql()->table('publication_person')
										->field('publication_id')
										->where(\npdc\lib\Db::joinVersion('publication', 'publication_person'))
										->where('organization_id', $values)
								)
						);
				}
			}
		}
		$q->order('date DESC, title')
			->field('publication.*')
			->field($q->expr('extract(YEAR FROM date)'), 'year')
			->field($q->expr("'Publication'"), 'content_type')->where('record_status', 'published')
			->field($q->dsql()
				->expr('CASE WHEN record_status = [] THEN TRUE ELSE FALSE END {}', ['draft', 'hasDraft'])
			);
		if($session->userLevel > NPDC_USER){
			if($session->userLevel === NPDC_ADMIN) {
				$q->field($q->dsql()->expr('TRUE {}', ['editor']))
					->where('publication.publication_version', 
					$q->dsql()->table(['ds2'=>'publication'])
						->field('MAX(publication_version)')
						->where('ds2.publication_id=publication.publication_id')
				);
			} elseif($session->userLevel === NPDC_EDITOR){
				$isEditor = $q->dsql()->table('publication_person')
					->field('publication_id')
					->where(\npdc\lib\Db::joinVersion('publication', 'publication_person'))
					->where('person_id', $session->userId)
					->where('editor');
				$q->field($q->dsql()
					->expr('CASE 
						WHEN creator=[] THEN TRUE
						WHEN EXISTS([]) THEN TRUE
						ELSE FALSE 
						END {}', [$session->userId, $isEditor, 'editor']
						)
					)->where('publication.publication_version', 
						$q->dsql()->table(['ds2'=>'publication'])
							->field('MAX(publication_version)')
							->where('ds2.publication_id=publication.publication_id')
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
			switch($filters['editorOptions'][0]){
				case 'all':
					break;
				case 'unpublished':
					$q->where('publication_version', 1);
				case 'draft':
					$q->where('record_status', 'draft');
				case 'edit':
					if($session->userLevel === NPDC_EDITOR){
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
	 * Get publication by id
	 *
	 * @param integer $id publication id
	 * @param integer|string $version either numeric version, or record status
	 * @return array publication details
	 */
	public function getById($id, $version='published'){
		return \npdc\lib\Db::get('publication', ['publication_id'=>$id, (is_numeric($version) ? 'publication_version' : 'record_status')=>$version]);
	}

	/**
	 * Get publication by uuid
	 *
	 * @param string $uuid the uuid
	 * @return array a publication
	 */
	public function getByUUID($uuid){
		return \npdc\lib\Db::get('publication', ['uuid'=>$uuid]);
	}
	
	/**
	 * Get publication by DOI
	 *
	 * @param string $doi DOI to find
	 * @return array a publication
	 */
	public function getByDOI($doi){
		return \npdc\lib\Db::get('publication', ['doi'=>$doi]);
	}
	
	/**
	 * Get persons for publication
	 *
	 * @param integer $id publication id
	 * @param integer $version publication version
	 * @return array list of persons
	 */
	public function getPersons($id, $version){
		$q = $this->dsql->dsql()
			->table('publication_person')->field('publication_person.*');
		return $q->join('person.person_id', 'person_id', 'left')
				->field($q->expr('CASE WHEN name IS NULL THEN free_person ELSE name END'), 'name')
			->join('organization.organization_id', 'organization_id', 'left')->field('organization_name')
			->where(\npdc\lib\Db::selectVersion('publication', $id, $version))
			->order('sort')
			->get();
	}
	
	/**
	 * Get formatted list of authors
	 *
	 * @param integer $publication_id publication id
	 * @param integer $publication_version publication version
	 * @param integer $names nummber of names to show before 'et al'
	 * @return string formatted list of authors
	 */
	public function getAuthors($publication_id, $publication_version, $names=2, $sep = ','){
		$q = $this->dsql->dsql()
			->table('publication_person');
		$res = $q->join('person.person_id', 'person_id', 'left')->field($q->expr('CASE 
				WHEN free_person IS NOT NULL THEN free_person 
				WHEN surname IS NULL THEN name 
				ELSE surname || \', \' || COALESCE(initials, given_name) 
			END'), 'name')
			->where(\npdc\lib\Db::selectVersion('publication', $publication_id, $publication_version))
			->order('sort')
			->get();
		$c = count($res);
		if($c === 1){
			$return = $res[0]['name'];
		} elseif ($c === 2){
			$return = $res[0]['name'].' &amp; '.$res[1]['name'];
		} else {
			$return = '';
			if(!is_numeric($names) || $names < 1 || is_nan($names)){
				$names = 1;
			}
			for($i=0;$i<min($c-1, $names);$i++){
				$return .= ($i>0 ? $sep.' ' : '').$res[$i]['name'];
			}
			if($c <= $names+1){
				$return .= $sep.' &amp; '.$res[$i]['name'];
			} else {
				$return .= $sep.' et al.';
			}
		}
		return $return;
	}

	/**
	 * Get keywords of publication
	 *
	 * @param integer $id publication id
	 * @param integer $version publication version
	 * @return array list of keywords
	 */
	public function getKeywords($id, $version){
		return $this->dsql->dsql()
			->table('publication_keyword')
			->where(\npdc\lib\Db::selectVersion('publication', $id, $version))
			->order('keyword')
			->get();
	}
	
	/**
	 * Get linked datasets
	 *
	 * @param integer $id publication id
	 * @param integer $version publication version
	 * @param boolean $published only show published datasets
	 * @return array list of datasets
	 */
	public function getDatasets($id, $version, $published = true){
		$q = $this->dsql->dsql()
			->table('dataset_publication')
			->join('dataset', \npdc\lib\Db::joinVersion('dataset', 'dataset_publication'), 'inner')
			->where(\npdc\lib\Db::selectVersion('publication', $id, $version));
		$q->order($q->expr('date_start DESC, dataset.dataset_id, '.\npdc\lib\Db::$sortByRecordStatus));
		if($published){
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
	 * Get linked projects
	 *
	 * @param integer $id publication id
	 * @param integer $version publication version
	 * @param boolean $published only show published projects
	 * @return array list of projects
	 */
	public function getProjects($id, $version, $published = true){
		$q = $this->dsql->dsql()
			->table('project_publication')
			->join('project', \npdc\lib\Db::joinVersion('project', 'project_publication'), 'inner')
			->where(\npdc\lib\Db::selectVersion('publication', $id, $version));
		$q->order($q->expr('date_start DESC, project.project_id, '.\npdc\lib\Db::$sortByRecordStatus));
		if($published){
			$q->where('record_status', 'published');
		} else {
			$q->where('project_version',
				$q->dsql()
					->table('project', 'a')
					->field('max(project_version)')
					->where('a.project_id=project.project_id')
				);
		}
		return $q->get();
	}
	
	/**
	 * Search publications
	 *
	 * @param string $string search string
	 * @param boolean $summary search in summary
	 * @param array $exclude list of publications ids to ignore
	 * @param boolean $includeDraft also search in drafts
	 * @return array list of publications matching filter
	 */
	public function search($string, $summary = false, $exclude = null, $includeDraft = false){
		$q = $this->dsql->dsql()
			->table('publication')
			->field('*');
		$q->field($q->expr('\'Publication\''), 'content_type')
			->order('date DESC');
		if(!empty($string)){
			$string = '%'.$string.'%';
			$operator = (\npdc\config::$db['type']==='pgsql' ? '~*' : 'LIKE');
			$s = $q->orExpr()
				->where('title', $operator, $string)
				->where('doi', $operator, $string);
			if($summary){
				$s->where('abstract', $operator, $string);
			}
			$q->where($s);
		}
		if(is_array($exclude) && count($exclude) > 0){
			$q->where('publication_id', 'NOT', $exclude);
		}
		if($includeDraft){
			$q->where('publication_version', $q->dsql()->table('publication', 'a')->field('max(publication_version)')->where('a.publication_id=publication.publication_id'));
		} else {
			$q->where('record_status', 'published');	
		}
		return $q->get();
	}

	/**
	 * Search by freely inputted organization
	 *
	 * @param string $search search string
	 * @return array list of publications matching filter
	 */
	public function searchByFreeOrganization($search){
		$q = $this->dsql->dsql()->table('publication_person')
			->join('publication.publication_id', 'publication_id', 'inner')
			->where(\npdc\lib\Db::joinVersion('publication', 'publication_person'))
			->where('free_organization', 'LIKE', '%'.$search.'%');
		return $q->get();
	}

	/**
	 * Search by freely inputted person
	 *
	 * @param string $search search string
	 * @return array list of publications matching filter
	 */
	public function searchByFreePerson($search){
		$q = $this->dsql->dsql()->table('publication_person')
			->join('publication.publication_id', 'publication_id', 'inner')
			->where(\npdc\lib\Db::joinVersion('publication', 'publication_person'))
			->where('free_person', 'LIKE', '%'.$search.'%');
		return $q->get();
	}
	
	/**
	 * Retun list of publication types
	 *
	 * @return array list of publication types
	 */
	public function getTypes(){
		return $this->dsql->dsql()->table('publication_type')->get();
	}

	/**
	 * Get publication type details based on id
	 *
	 * @param integer $id publication type id
	 * @return array a single publication type
	 */
	public function getTypeById($id){
		return $this->dsql->dsql()
			->table('publication_type')
			->where('publication_type_id', $id)
			->get()[0];
	}

	/**
	 * Return formatted citation
	 *
	 * @param integer|array $publication either id or full publication record
	 * @param string|integer $version (optional) version number of type
	 * @param boolean $wrap (optional, default true) whether to wrap the citation in a p-element or not
	 * @return string formatted citation
	 */
	public function getCitation($publication, $version = 'published', $wrap = true, $link=true){
		if(is_numeric($publication)){
			$publication = $this->getById($publication, $version);
		}
		return ($wrap ? '<p>' : '').$this->getAuthors($publication['publication_id'], $publication['publication_version'], 2).', '
		. ($publication['year'] ?? substr($publication['date'], 0, 4)).'. '
		. ($link ? '<a href="'.BASE_URL.'/publication/'.$publication['uuid'].'">' : '').$publication['title'].($link ? '</a>' : '').(in_array(substr($publication['title'],-1), ['.','?']) ? '' : '.').' <i>'
		. $publication['journal'].'</i> '.$publication['volume']
		. (empty($publication['issue']) ? '' : ' ('.$publication['issue'].')')
		. (empty($publication['pages'] && $publication['pages'] !== '-') ? '' :', '.$publication['pages'])
		.($wrap ? '</p>' : '');

	}
	/**
	 * SETTERS
	 */

	protected function parseGeneral($data, $action){
		$fields = ['title','abstract','journal','volume', 'issue','pages','isbn','doi','date','url','record_status', 'creator', 'publication_type_id'];
		if($action === 'insert'){
			array_push($fields, 'publication_version');
			if(is_numeric($data['publication_id'])){
				array_push($fields, 'publication_id');
			}
		}
		$values = [];
		foreach($fields as $field){
			switch($field){
				case 'date':
					if(is_array($data['date'])){
						$values['date'] = $data['date'][0];
					} else {
						$values['date'] = $data['date'];
					}
					break;
				case 'record_status':
				case 'creator':
					if(empty($data[$field])){
						break;
					}
				default:
					$values[$field] = empty($data[$field]) ? null : $data[$field];
					
			}
		}
		return $values;
	}

	public function insertProject($data){
		\npdc\lib\Db::insert('project_publication', $data);
	}

	public function deleteProject($publication_id, $version, $currentProjects){
		$q = $this->dsql->dsql()
			->table('project_publication')
			->where('publication_id', $publication_id)
			->where('publication_version_max IS NULL');
		if(count($currentProjects) > 0){
			$q->where('project_id', 'NOT', $currentProjects);
		}
		$q->set('publication_version_max', $version)
			->update();
		return true;
	}
	
	public function insertDataset($data){
		\npdc\lib\Db::insert('dataset_publication', $data);
	}

	public function deleteDataset($publication_id, $version, $currentDatasets){
		$q = $this->dsql->dsql()
			->table('dataset_publication')
			->where('publication_id', $publication_id)
			->where('publication_version_max IS NULL');
		if(count($currentDatasets) > 0){
			$q->where('dataset_id', 'NOT', $currentDatasets);
		}
		$q->set('publication_version_max', $version)
			->update();
		return true;
	}	
}