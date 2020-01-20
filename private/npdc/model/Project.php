<?php

/**
 * Project model
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\model;

class Project{
	private $fpdo;
	private $dsql;

	/**
	 * Constructor
	 */
	public function __construct(){
		$this->fpdo = \npdc\lib\Db::getFPDO();
		$this->dsql = \npdc\lib\Db::getDSQLcon();
	}
	
	/**
	 * GETTERS
	 */

	/**
	 * Get list of projects
	 *
	 * @param array|null $filters (Optional) filters to filter projects by
	 * @return array List of projects
	 */
	public function getList($filters=null){
		global $session;
		$q = $this->fpdo->from('project')->where('record_status', 'published')->select('date_start || \' - \' || date_end period')->select('"Project" as content_type');
		$q2 = $this->fpdo
			->from('project')->select('date_start || \' - \' || date_end period')
			->join('(SELECT project_id, MAX(project_version) AS project_version FROM project GROUP BY project_id) a USING (project_id, project_version)');
		if($session->userLevel < NPDC_ADMIN){
			$q2->leftJoin('(SELECT * FROM project_person WHERE person_id = '.$session->userId.' AND editor) b ON (project.project_id=b.project_id AND project_version_min<=project_version AND (project_version_max IS NULL OR project_version_max >= project_version))')
				->where('(editor OR creator='.$session->userId.')');
		}
		if(!is_null($filters)){
			foreach($filters as $filter=>$values){
				if((is_array($values) && count($values) === 0) || empty($values)){
					continue;
				}
				switch($filter){
					case 'region':
						$q->where('region', $values);
						$q2->where('region', $values);
						break;
						
					case 'period':
						//use values swapped, include all records with start date before end date of filter and end date after start date of filter
						if(!empty($values[1])){
							$q->where('date_start <= ?', $values[1]);
							$q2->where('date_start <= ?', $values[1]);
						}
						if(!empty($values[0])){
							$q->where('date_end >= ?', $values[0]);
							$q2->where('date_end >= ?', $values[0]);
						}
						break;
						
					case 'organization':
						$q->where('project.project_id IN (SELECT DISTINCT(project_id) FROM project_person WHERE organization_id IN ('.implode(',', $values).'))');
						$q2->where('project.project_id IN (SELECT DISTINCT(project_id) FROM project_person WHERE organization_id IN ('.implode(',', $values).'))');
						break;

					case 'program':
						$q->where('program_id', $values);
						$q2->where('program_id', $values);
						break;
				}
			}
		}
		
		$published = $q->fetchAll('project_id');
		$final = [];
		if($session->userLevel > NPDC_PUBLIC){
			$editor = $q2->fetchAll('project_id');
			if(empty($filters['editorOptions']) || $filters['editorOptions'][0] === 'all'){
				foreach($published as $id=>$row){
					$row['editor'] = array_key_exists($id, $editor);
					$row['hasDraft'] = array_key_exists($id, $editor) && $editor[$id]['record_status'] === 'draft';
					$final[$row['date_start'].'_'.$row['date_end'].'_'.$id] = $row;
					unset($editor[$id]);
				}
			} 
			foreach($editor as $id=>$row){
				$row['hasDraft'] = true;
				if(isset($filters['editorOptions']) && $filters['editorOptions'][0] !== 'all'){
					if(array_key_exists($id, $published)){
						$row = $published[$id];
						$row['hasDraft'] = $editor[$id]['record_status'] === 'draft';
					}
				}
				$row['editor'] = true;
				if(empty($filters['editorOptions'])
					|| $filters['editorOptions'][0] === 'all' 
					|| $filters['editorOptions'][0] === 'edit' 
					|| ($filters['editorOptions'][0] === 'draft' && $row['hasDraft']) 
					|| ($filters['editorOptions'][0] === 'unpublished' && $row['record_status'] === 'draft')
				){
					$final[$row['date_start'].'_'.$row['date_end'].'_'.$id] = $row;
				}
			}
		} else {
			foreach($published as $id=>$row){
				$final[$row['date_start'].'_'.$row['date_end'].'_'.$id] = $row;
			}
		}
		krsort($final);
		return $final;
	}
	
	/**
	 * retrieve a project by its id
	 * 
	 * @param integer $id project id
	 * @param integer|string $version either numeric version, or record status
	 * @return array a project
	 */
	public function getById($id, $version='published'){
		if(!is_numeric($id)){
			$return = false;
		} else {
			$return = $this->fpdo
				->from('project', $id)
				->leftJoin('program')->select('program.*')
				->where(is_numeric($version) ? 'project_version' : 'record_status', $version)
				->fetch();
		}
		return $return;
	}

	/**
	 * Get project by uuid
	 *
	 * @param string $uuid the uuid
	 * @return array a project
	 */
	public function getByUUID($uuid){
		return $this->fpdo->from('project')->where('uuid', $uuid)->fetch();
	}
	
	/**
	 * Get parent project(s) of project
	 *
	 * @param integer $id child project id
	 * @return array parent projects
	 */
	public function getParents($id){
		return $this->fpdo
			->from('project_project')
			->join('project ON project_id=parent_project_id')->select('project.*, date_start || \' - \' || date_end period')
			->where('record_status', 'published')
			->where('child_project_id', $id)
			->fetchAll();
	}
	
	/**
	 * Get child project(s) of projects
	 *
	 * @param integer $id parent project id
	 * @return array child projects
	 */
	public function getChildren($id){
		return $this->fpdo
			->from('project_project')
			->join('project ON project_id=child_project_id')->select('project.*, date_start || \' - \' || date_end period')
			->where('record_status', 'published')
			->where('parent_project_id', $id)
			->fetchAll();
	}
	
	/**
	 * get list of persons of project
	 * 
	 * @param string $id project id
	 * @param string $version project version
	 * @return array list of persons
	 */
	public function getPersons($id, $version){
		return $this->fpdo
			->from('project_person')
			->join('person USING(person_id)')->select('name')
			->leftJoin('organization ON project_person.organization_id=organization.organization_id')->select('organization_name')
			->where('project_id = ?', $id)
			->where('project_version_min <= ?', $version)
			->where('(project_version_max IS NULL OR project_version_max>= ?)', $version)
			->orderBy('sort')
			->fetchAll();
	}
	
	/**
	 * get list of datasets linked to project
	 * 
	 * @param integer $id project id
	 * @param integer $version project version
	 * @param boolean $published only show published datasets
	 * @return array list of datasets
	*/
	public function getDatasets($id, $version, $published = true){
		$q = $this->fpdo
			->from('dataset_project')
			->join('dataset '
				. 'ON dataset.dataset_id=dataset_project.dataset_id '
				. 'AND dataset.dataset_version >= dataset_project.dataset_version_min '
				. 'AND (dataset_project.dataset_version_max IS NULL OR dataset.dataset_version <= dataset_project.dataset_version_max)')
			->select('dataset.*, date_start || \' - \' || date_end dates')
			->where('project_id = ?', $id)
			->where('project_version_min <= ?', $version)
			->where('(project_version_max IS NULL OR project_version_max>= ?)', $version)
			->orderBy('date_start DESC, dataset.dataset_id, '.\npdc\lib\Db::$sortByRecordStatus);
		if($published){
			$q->where('record_status', 'published');
		} else {
			$q->join('(SELECT dataset_id, MAX(dataset_version) dataset_version FROM dataset GROUP BY dataset_id) AS a ON a.dataset_id=dataset.dataset_id AND a.dataset_version=dataset.dataset_version');
		}
		return $q->fetchAll();
	}
	
	/**
	 * Get the publications linked to a project
	 *
	 * @param integer $id project id
	 * @param integer $version version number of project
	 * @param boolean $published only show published publications or also drafts
	 * @return array list of publications
	 */
	public function getPublications($id, $version, $published = true){
		$q = $this->fpdo
			->from('project_publication')
			->join('publication '
				. 'ON publication.publication_id=project_publication.publication_id '
				. 'AND publication.publication_version >= project_publication.publication_version_min '
				. 'AND (project_publication.publication_version_max IS NULL OR publication.publication_version <= project_publication.publication_version_max)')
			->select('publication.*, EXTRACT(year FROM date) AS year')
			->where('project_id = ?', $id)
			->where('project_version_min <= ?', $version)
			->where('(project_version_max IS NULL OR project_version_max>= ?)', $version)
			->orderBy('date DESC, publication.publication_id, '.\npdc\lib\Db::$sortByRecordStatus);
		if($published){
			$q->where('record_status', 'published');
		} else {
			$q->join('(SELECT publication_id, MAX(publication_version) publication_version FROM publication GROUP BY publication_id) AS a ON a.publication_id=publication.publication_id AND a.publication_version=publication.publication_version')
				->where('record_status', ['draft', 'published']);
		}
		return $q->fetchAll();
	}

	/**
	 * Get keywords of project
	 *
	 * @param integer $id project id
	 * @param integer $version project version
	 * @return array list of keywords
	 */	
	public function getKeywords($id, $version){
		return $this->fpdo
			->from('project_keyword')
			->where('project_id = ?', $id)
			->where('project_version_min <= ?', $version)
			->where('(project_version_max IS NULL OR project_version_max>= ?)', $version)
			->orderBy('keyword')
			->fetchAll();
	}
	

	public function getLinks($id, $version){
		return $this->fpdo
			->from('project_link')
			->where('project_id = ?', $id)
			->where('project_version_min <= ?', $version)
			->orderBy('text')
			->fetchAll();
	}
	
	/**
	 * get urls linked to project
	 * @param string $id project id
	 * @return array array of urls
	 */
	public function getUrls($id){
		return $this->fpdo
			->from('project_link')
			->where('project_id', $id)
			->orderBy('sort');
	}
	
	/**
	 * function for the search page
	 * @param string $string
	 * @return array projects matching $string
	 */
	public function search($string, $summary = false, $exclude = null, $includeDraft = false){
		$idString = implode('[.]?', preg_replace("/[^. \-0-9a-zA-Z]/", " ", str_split($string)));
		$string = '%'.$string.'%';
		$q = $this->fpdo
			->from('project')
			->select('project_id, date_start || \' - \' || date_end AS date')
			->select('\'Project\' AS content_type')
			->orderBy('date DESC');
		if(!empty($string)){
			$operator = (\npdc\config::$db['type']==='pgsql' ? '~*' : 'LIKE');
			if($summary){
				$q->where('(title '.$operator.' :search1 OR summary '.$operator.' :search2 OR nwo_project_id '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :search3 OR acronym '.$operator.' :search4)', [':search1'=>$string, ':search2'=>$string, ':search3'=>$idString, ':search4'=>$string]);
			} else {
				$q->where('(title '.$operator.' :search1 OR nwo_project_id '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :search2 OR acronym '.$operator.' :search3)', [':search1'=>$string, ':search2'=>$idString, ':search3'=>$string]);
			}
		}
		if(is_array($exclude) && count($exclude) > 0){
			$q->where('project_id NOT', $exclude);
		}
		if($includeDraft) {
			$q->join('(SELECT project_id, MAX(project_version) as project_version FROM project GROUP BY project_id) a USING (project_id, project_version)');
		} else {
			$q->where('record_status = \'published\'');	
		}
		return $q->fetchAll();
	}
	
	/**
	 * Check if person is allowed to edit record
	 *
	 * @param integer $project_id id of project
	 * @param integer $person_id id of person
	 * @return boolean user is allowed to edit
	 */
	public function isEditor($project_id, $person_id){
		return is_numeric($project_id) && is_numeric($person_id)
			? (
				$this->fpdo
				->from('project_person')
				->where('project_id', $project_id)
				->where('person_id', $person_id)
				->where('project_version_max IS NULL')
				->where('editor')
				->count() > 0
			) || (
				$this->fpdo
				->from('project')
				->where('project_id', $project_id)
				->where('creator', $person_id)
				->where('record_status IN (\'draft\', \'published\')')
				->count() > 0
			)
			: false;
	}
	
	/**
	 * Get all available versions of project
	 *
	 * @param integer $project_id project id
	 * @return array list of available versions with status of each version
	 */
	
	public function getVersions($project_id){
		return $this->fpdo
			->from('project', $project_id)->select(null)
			->select('project_version, record_status, uuid')
			->orderBy('project_version DESC')
			->fetchAll();
	}

	/**
	 * SETTERS
	 */

	/**
	 * store new version of record
	 * @param array $data
	 */
	public function insertGeneral($data){
		$values = $this->parseGeneral($data, 'insert');
		$r = $this->fpdo->from('project')->where($values)->fetch();
		if($r === false){
			$this->fpdo->insertInto('project', $values)->execute();
			$r = $this->fpdo->from('project')->where($values)->fetch();
		}
		$this->fpdo
			->update('project')
			->set(['uuid'=>\Lootils\Uuid\Uuid::createV5(\npdc\config::$UUIDNamespace ?? \Lootils\Uuid\Uuid::createV4(), 'project/'.$r['project_id'].'/'.$r['project_version'])->getUUID()])
			->where('project_id', $r['project_id'])
			->where('project_version', $r['project_version'])
			->execute();
		return $r['project_id'];
	}
	
	/**
	 * 
	 * @param array $data
	 * @param string $id
	 * @param integer $version
	 * @return type
	 */
	public function updateGeneral($data, $id, $version){
		$values = $this->parseGeneral($data, 'update');
		return $this->fpdo
			->update('project')
			->set($values)
			->where('project_id', $id)
			->where('project_version', $version)
			->execute();
	}
	
	public function insertPerson($data){
		return $this->fpdo
			->insertInto('project_person', $data)
			->execute();
	}
	
	public function updatePerson($record, $data, $version){
		$oldRecord = $this->fpdo
			->from('project_person')
			->where($record)
			->fetch();
		
		$createNew = false;
		$updateEditor = false;
		foreach($data as $key=>$val){
			if($val != $oldRecord[$key]){
				if($key === 'editor'){
					$updateEditor = true;
				} else {
					$createNew = true;
				}
			}
		}
		if($oldRecord['project_version_min'] === $version && ($createNew || $updateEditor)){
			$return = $this->fpdo
				->update('project_person')
				->set($data)
				->where($record)
				->execute();
		} elseif($createNew){
			$this->fpdo
				->update('project_person')
				->set('project_version_max', $version-1)
				->where($record)
				->execute();
			$this->insertPerson(array_merge($data, $record, ['project_version_min'=>$version]));
			$return = true;
		} elseif($updateEditor) {
			$return = $this->fpdo
				->update('project_person')
				->set('editor', $data['editor'])
				->where($record)
				->execute();
		} else {
			$return = true;
		}
		return $return;
	}
	
	public function deletePerson($project_id, $version, $currentPersons){
		$q = $this->fpdo
			->update('project_person')
			->set('project_version_max', $version)
			->where('project_id', $project_id)
			->where('project_version_max', null);
		if(count($currentPersons) > 0){
			foreach($currentPersons as $person){
				if(!is_numeric($person)){
					die('Hacking attempt!');
				}
			}
			if(count($currentPersons) === 1){
				$q->where('person_id <> ?',$currentPersons[0]);
			} else {
				$q->where('person_id NOT IN ('.implode(',',$currentPersons).')');
			}
		}
		$q->execute();
		$this->fpdo
			->deleteFrom('project_person')
			->where('project_version_max < project_version_min')
			->execute();
		return true;
	}
	
	public function insertKeyword($word, $id, $version){
		return $this->fpdo
			->insertInto('project_keyword')
			->values(['project_id'=>$id,
				'project_version_min'=>$version,
				'keyword'=>$word])
			->execute();
	}
	
	public function deleteKeyword($word, $id, $version){
		$this->fpdo
			->update('project_keyword')
			->set('project_version_max', $version)
			->where('project_id', $id)
			->where('keyword', $word)
			->where('project_version_max', null)
			->execute();
		$this->fpdo
			->deleteFrom('project_keyword')
			->where('project_version_max < project_version_min')
			->execute();
	}
	
	public function insertLink($data){
		return $this->fpdo
			->insertInto('project_link')
			->values($data)
			->execute();
	}
	
	public function updateLink($id, $data){
		return $this->fpdo
			->update('project_link', $data, $id)
			->execute();
	}
	
	public function deleteLink($project_id, $keep, $version){
		$q = $this->fpdo
			->update('project_link')
			->set('project_version_max', $version)
			->where('project_version_max', null)
			->where('project_id', $project_id);
		if(is_array($keep) && count($keep) > 0){
			foreach($keep as $id){
				if(!is_numeric($id)){
					die('Hacking attempt');
				}
			}
			$q->where('project_link_id NOT IN ('.implode(',', $keep).')');
		}
		$q->execute();
		$this->fpdo
			->deleteFrom('project_link')
			->where('project_version_max < project_version_min')
			->execute();
	}
	
	public function insertPublication($data){
		$this->fpdo
			->insertInto('project_publication')
			->values($data)
			->execute();
	}
	public function deletePublication($project_id, $version, $currentPublications){
		$q = $this->fpdo
			->update('project_publication')
			->set('project_version_max', $version)
			->where('project_id', $project_id)
			->where('publication_version_max', null);
		if(count($currentPublications) > 0){
			if(count($currentPublications) === 1){
				$q->where('publication_id <> ?',$currentPublications[0]);
			} else {
				$q->where('publication_id NOT', $currentPublications);
			}
		}
		$q->execute();
		/*\npdc\lib\Db::executeQuery('UPDATE project_publication SET project_version_max=project_version FROM project WHERE publication_version_max IS NOT NULL AND project.project_id=project_publication.project_id AND record_status=\'published\'');		
		\npdc\lib\Db::executeQuery('UPDATE project_publication SET publication_version_max=publication_version FROM publication WHERE project_version_max IS NOT NULL AND publication.publication_id=project_publication.publication_id AND record_status=\'published\'');*/
		
		$this->fpdo
			->deleteFrom('project_publication')
			->where('project_version_max < project_version_min')
			->execute();
		return true;
	}
	
	public function insertDataset($data){
		$this->fpdo
			->insertInto('dataset_project')
			->values($data)
			->execute();
	}
	public function deleteDataset($project_id, $version, $currentDatasets){
		$q = $this->fpdo
			->update('dataset_project')
			->set('project_version_max', $version)
			->where('project_id', $project_id)
			->where('dataset_version_max', null);
		if(count($currentDatasets) > 0){
			if(count($currentDatasets) === 1){
				$q->where('dataset_id <> ?',$currentDatasets[0]);
			} else {
				$q->where('dataset_id NOT', $currentDatasets);
			}
		}
		$q->execute();
		/*\npdc\lib\Db::executeQuery('UPDATE dataset_project SET project_version_max=project_version FROM project WHERE dataset_version_max IS NOT NULL AND project.project_id=project_dataset.project_id AND record_status=\'published\'');		
		\npdc\lib\Db::executeQuery('UPDATE dataset_project SET dataset_version_max=dataset_version FROM dataset WHERE project_version_max IS NOT NULL AND dataset.dataset_id=project_dataset.dataset_id AND record_status=\'published\'');*/
		
		$this->fpdo
			->deleteFrom('dataset_project')
			->where('project_version_max < project_version_min')
			->execute();
		return true;
	}
	
	public function setStatus($project_id, $old, $new, $comment = null){
		$r = $this->fpdo->from('project')
			->where('project_id', $project_id)
			->where('record_status', $old)
			->fetch();
		if($r !== false){
			$q = $this->fpdo
				->update('project')
				->set('record_status', $new);
			if($new === 'published'){
				$q->set('published', date("Y-m-d H:i:s", time()));
			}
			$return = $q
				->where('project_id', $project_id)
				->where('record_status', $old)
				->execute();
			$this->fpdo->insertInto('record_status_change', ['project_id'=>$project_id, 'version'=>$r['project_version'], 'old_state'=>$old, 'new_state'=>$new, 'person_id'=>$_SESSION['user']['id'], 'comment'=>$comment])->execute();
		}
		return $return;
	}
	
	public function getLastStatusChange($project_id, $version, $state = null){
		$q = $this->fpdo
			->from('record_status_change')
			->join('person')->select('person.*')
			->where('project_id', $project_id)
			->where('version', $version)
			->orderBy('datetime DESC');
		if($state !== null){
			$q->where('new_state', $state);
		}
		return $q->fetch();
	}
	public function getStatusChanges($project_id, $version){
		return $this->fpdo
			->from('record_status_change')
			->join('person')->select('person.*')
			->where('project_id', $project_id)
			->where('version', $version)
			->orderBy('datetime DESC')
			->fetchAll();
	}

	/**
	 * 
	 * @param array $data
	 * @param string $action Either update or insert
	 */
	private function parseGeneral($data, $action){
		$fields = ['nwo_project_id','title','acronym','region','summary','program_id','date_start','date_end','research_type','science_field','record_status', 'creator', 'npp_theme_id'];
		if($action === 'insert'){
			array_push($fields, 'project_version');
			if(is_numeric($data['project_id'])){
				array_push($fields, 'project_id');
			}
		}
		$values = [];
		foreach($fields as $field){
			switch($field){
				case 'date_start':
					$values[$field] = $data['period'][0] ?? $data['date_start'];
					break;
				case 'date_end':
					$values[$field] = $data['period'][1] ?? $data['date_end'];
					break;
				case 'creator':
				case 'record_status':
					if(empty($data[$field])){
						continue;
					}
				default:
					$values[$field] = empty($data[$field]) ? null : $data[$field];
					
			}
		}
		return $values;
	}
}
