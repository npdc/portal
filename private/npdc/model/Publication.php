<?php

/**
 * Publication model
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\model;

class Publication{
	private $fpdo;

	/**
	 * Constructor
	 */
	public function __construct(){
		$this->fpdo = \npdc\lib\Db::getFPDO();
	}
	
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
		$q = $this->fpdo->from('publication')->select('extract(YEAR FROM date) as year')->where('record_status', 'published');
		$q2 = $this->fpdo
			->from('publication')
			->select('extract(YEAR FROM date) as year')
			->join('(SELECT publication_id, MAX(publication_version) AS publication_version FROM publication GROUP BY publication_id) a USING (publication_id, publication_version)');
		if($session->userLevel < NPDC_ADMIN){
			$q2->leftJoin('(SELECT * FROM publication_person WHERE person_id = '.$session->userId.' AND editor) b ON (publication.publication_id=b.publication_id AND publication_version_min<=publication_version AND (publication_version_max IS NULL OR publication_version_max >= publication_version))')
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
						
					case 'year':
						//use values swapped, include all records with start date before end date of filter and end date after start date of filter
						if(!empty($values[1])){
							$q->where('date <= ?', $values[1]);
							$q2->where('date <= ?', $values[1]);
						}
						if(!empty($values[0])){
							$q->where('date >= ?', $values[0]);
							$q2->where('date >= ?', $values[0]);
						}
						break;
						
					case 'organization':
						$q->where('publication.publication_id IN (SELECT DISTINCT(publication_id) FROM publication_person WHERE organization_id IN ('.implode(',', $values).'))');
						$q2->where('publication.publication_id IN (SELECT DISTINCT(publication_id) FROM publication_person WHERE organization_id IN ('.implode(',', $values).'))');
						break;
				}
			}
		}
		
		$published = $q->fetchAll('publication_id');
		$final = [];
		if($session->userLevel > NPDC_PUBLIC){
			$editor = $q2->fetchAll('publication_id');
			if(empty($filters['editorOptions']) || $filters['editorOptions'][0] === 'all'){
				foreach($published as $id=>$row){
					$row['editor'] = array_key_exists($id, $editor);
					$row['hasDraft'] = array_key_exists($id, $editor) && $editor[$id]['record_status'] === 'draft';
					$final[$row['date'].'_'.$id] = $row;
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
					$final[$row['date'].'_'.$id] = $row;
				}
			}
		} else {
			foreach($published as $id=>$row){
				$final[$row['date'].'_'.$id] = $row;
			}
		}
		krsort($final);
		return $final;
	}
	
	/**
	 * Get publication by id
	 *
	 * @param integer $id publication id
	 * @param integer|string $version either numeric version, or record status
	 * @return array publication details
	 */
	public function getById($id, $version='published'){
		if(!is_numeric($id)){
			$return = false;
		} else {
			$return = $this->fpdo
				->from('publication', $id)
				->where(is_numeric($version) ? 'publication_version' : 'record_status', $version)
				->fetch();
		}
		return $return;
	}

	/**
	 * Get publication by uuid
	 *
	 * @param string $uuid the uuid
	 * @return array a publication
	 */
	public function getByUUID($uuid){
		return $this->fpdo->from('publication')->where('uuid', $uuid)->fetch();
	}
	
	/**
	 * Get publication by DOI
	 *
	 * @param string $doi DOI to find
	 * @return array a publication
	 */
	public function getByDOI($doi){
		return $this->fpdo
			->from('publication')
			->where('doi', $doi)
			->fetch();
	}
	
	/**
	 * Get persons for publication
	 *
	 * @param integer $id publication id
	 * @param integer $version publication version
	 * @return array list of persons
	 */
	public function getPersons($id, $version){
		return $this->fpdo
			->from('publication_person')
			->leftJoin('person USING(person_id)')->select('CASE WHEN name IS NULL THEN free_person ELSE name END AS name')
			->leftJoin('organization ON publication_person.organization_id=organization.organization_id')->select('organization_name')
			->where('publication_id = ?', $id)
			->where('publication_version_min <= ?', $version)
			->where('(publication_version_max IS NULL OR publication_version_max>= ?)', $version)
			->orderBy('sort')
			->fetchAll();
	}
	
	/**
	 * Get formatted list of authors
	 *
	 * @param integer $publication_id publication id
	 * @param integer $publication_version publication version
	 * @param integer $names nummber of names to show before 'et al'
	 * @return string formatted list of authors
	 */
	public function getAuthors($publication_id, $publication_version, $names=2){
		$res = $this->fpdo
			->from('publication_person')
			->leftJoin('person')->select('CASE 
				WHEN free_person IS NOT NULL THEN free_person 
				WHEN surname IS NULL THEN name 
				ELSE surname || \', \' || COALESCE(initials, given_name) 
			END AS name')
			->where('publication_id', $publication_id)
			->where('publication_version_min <= ?', $publication_version)
			->where('(publication_version_max IS NULL OR publication_version_max >= ?)', $publication_version)
			->orderBy('sort')
			->fetchAll();
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
				$return .= ($i>0 ? ', ' : '').$res[$i]['name'];
			}
			if($c <= $names+1){
				$return .= ', &amp; '.$res[$i]['name'];
			} else {
				$return .= ', et al.';
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
		return $this->fpdo
			->from('publication_keyword')
			->where('publication_id = ?', $id)
			->where('publication_version_min <= ?', $version)
			->where('(publication_version_max IS NULL OR publication_version_max>= ?)', $version)
			->orderBy('keyword')
			->fetchAll();
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
		$q = $this->fpdo
			->from('dataset_publication')
			->join('dataset '
				. 'ON dataset.dataset_id=dataset_publication.dataset_id '
				. 'AND dataset.dataset_version >= dataset_publication.dataset_version_min '
				. 'AND (dataset_publication.dataset_version_max IS NULL OR dataset.dataset_version <= dataset_publication.dataset_version_max)')
			->select('dataset.*, date_start || \' - \' || date_end dates')
			->where('publication_id = ?', $id)
			->where('publication_version_min <= ?', $version)
			->where('(publication_version_max IS NULL OR publication_version_max>= ?)', $version)
			->orderBy('date_start DESC, dataset.dataset_id, '.\npdc\lib\Db::$sortByRecordStatus);
		if($published){
			$q->where('record_status', 'published');
		} else {
			$q->join('(SELECT dataset_id, MAX(dataset_version) dataset_version FROM dataset GROUP BY dataset_id) AS a ON a.dataset_id=dataset.dataset_id AND a.dataset_version=dataset.dataset_version');
		}
		return $q->fetchAll();
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
		$q = $this->fpdo
			->from('project_publication')
			->join('project '
				. 'ON project.project_id=project_publication.project_id '
				. 'AND project.project_version >= project_publication.project_version_min '
				. 'AND (project_publication.project_version_max IS NULL OR project.project_version <= project_publication.project_version_max)')
			->select('project.*, date_start || \' - \' || date_end period')
			->where('publication_id = ?', $id)
			->where('publication_version_min <= ?', $version)
			->where('(publication_version_max IS NULL OR publication_version_max>= ?)', $version)
			->orderBy('date_start DESC, project.project_id, '.\npdc\lib\Db::$sortByRecordStatus);
		if($published){
			$q->where('record_status', 'published');
		} else {
			$q->join('(SELECT project_id, MAX(project_version) project_version FROM project GROUP BY project_id) AS a ON a.project_id=project.project_id AND a.project_version=project.project_version');
		}
		return $q->fetchAll();
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
		$q = $this->fpdo
			->from('publication')
			->select('\'Publication\' AS content_type')
			->orderBy('date DESC');
		if(!empty($string)){
			$string = '%'.$string.'%';
			$operator = (\npdc\config::$db['type']==='pgsql' ? '~*' : 'LIKE');
			if($summary){
				$q->where('(title '.$operator.' :search1 OR abstract '.$operator.' :search2 OR doi '.$operator.' :search3)', [':search1'=>$string, ':search2'=>$string, ':search3'=>$string]);
			} else {
				$q->where('(title '.$operator.' :search OR doi '.$operator.' :search2', [':search'=>$string, ':search2'=>$string]);
			}
		}
		if(is_array($exclude) && count($exclude) > 0){
			$q->where('publication_id NOT', $exclude);
		}
		if($includeDraft){
			$q->join('(SELECT publication_id, MAX(publication_version) as publication_version FROM publication GROUP BY publication_id) AS a USING (publication_id, publication_version)');
		} else {
			$q->where('record_status = \'published\'');
		}
		return $q->fetchAll();
	}
	
	/**
	 * Check if person is allowed to edit record
	 *
	 * @param integer $publication_id id of publication
	 * @param integer $person_id id of person
	 * @return boolean user is allowed to edit
	 */
	public function isEditor($publication_id, $person_id){
		return is_numeric($publication_id)
			? (
				$this->fpdo
				->from('publication_person')
				->where('publication_id', $publication_id)
				->where('person_id', $person_id)
				->where('publication_version_max IS NULL')
				->count() > 0
			) || (
				$this->fpdo
				->from('publication')
				->where('publication_id', $publication_id)
				->where('creator', $person_id)
				->where('record_status IN (\'draft\', \'published\')')
				->count() > 0
			)
			: false;
	}
	
	/**
	 * Get all available versions of publication
	 *
	 * @param integer $publication_id publication id
	 * @return array list of available versions with status of each version
	 */
	public function getVersions($publication_id){
		return $this->fpdo
			->from('publication', $publication_id)->select(null)
			->select('publication_version, record_status, uuid')
			->orderBy('publication_version DESC')
			->fetchAll();
	}

	/**
	 * Get last status changed of the publication version
	 *
	 * @param integer $publication_id publication id
	 * @param integer $version publication version
	 * @param string|null $state new state to look for
	 * @return array status change with person
	 */
	public function getLastStatusChange($publication_id, $version, $state = null){
		$q = $this->fpdo
			->from('record_status_change')
			->join('person')->select('person.*')
			->where('publication_id', $publication_id)
			->where('version', $version)
			->orderBy('datetime DESC');
		if($state !== null){
			$q->where('new_state', $state);
		}
		return $q->fetch();
	}
	
	/**
	 * Get list of status changes of the publication version
	 *
	 * @param integer $publication_id publication id
	 * @param integer $version publication version
	 * @return array list of status changes with persons details
	 */

	public function getStatusChanges($publication_id, $version){
		return $this->fpdo
			->from('record_status_change')
			->join('person')->select('person.*')
			->where('publication_id', $publication_id)
			->where('version', $version)
			->orderBy('datetime DESC')
			->fetchAll();
	}

	/**
	 * Return formatted citation
	 *
	 * @param integer|array $publication either id or full publication record
	 * @param string|integer $version (optional) version number of type
	 * @return string formatted citation
	 */
	public function getCitation($publication, $version = 'published'){
		if(is_numeric($publication)){
			$publication = $this->getById($publication, $version);
		}
		return '<p>'.$this->getAuthors($publication['publication_id'], $publication['publication_version'], 2).', '
		. ($publication['year'] ?? substr($publication['date'], 0, 4)).'. '
		. '<a href="'.BASE_URL.'/publication/'.$publication['uuid'].'">'.$publication['title'].'</a>'.(in_array(substr($publication['title'],-1), ['.','?']) ? '' : '.').' <i>'
		. $publication['journal'].'</i> '.$publication['volume']
		. (empty($publication['issue']) ? '' : ' ('.$publication['issue'].')')
		. (empty($publication['pages'] && $publication['pages'] !== '-') ? '' :', '.$publication['pages'])
		. '</p>';

	}
	/**
	 * SETTERS
	 */

	private function parseData($data, $action){
		$fields = ['title','abstract','journal','volume', 'issue','pages','isbn','doi','date','url','record_status', 'creator'];
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
						continue;
					}
				default:
					$values[$field] = empty($data[$field]) ? null : $data[$field];
					
			}
		}
		return $values;
	}

	
	public function insertGeneral($data){
		$values = $this->parseData($data, 'insert');
		$r = $this->fpdo->from('publication')->where($values)->fetch();
		if($r === false){
			$this->fpdo->insertInto('publication', $values)->execute();
			$r = $this->fpdo->from('publication')->where($values)->fetch();
		}
		$this->fpdo
			->update('publication')
			->set(['uuid'=>\Lootils\Uuid\Uuid::createV5(\npdc\config::$UUIDNamespace ?? \Lootils\Uuid\Uuid::createV4(),'publication/'.$r['publication_id'].'/'.$r['publication_version'])->getUUID()])
			->where('publication_id', $r['publication_id'])
			->where('publication_version', $r['publication_version'])
			->execute();
		return $r['publication_id'];
	}
	
	public function updateGeneral($data, $id, $version){
		$values = $this->parseData($data, 'update');
		return $this->fpdo
			->update('publication')
			->set($values)
			->where('publication_id', $id)
			->where('publication_version', $version)
			->execute();
	}
	
	public function insertPerson($data){
		var_dump($data);
		return \npdc\lib\Db::insertReturnId('publication_person', $data);
	}
	
	public function updatePerson($record, $data, $version){
		$oldRecord = $this->fpdo
			->from('publication_person', $record)
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
		if($oldRecord['publication_version_min'] === $version && ($createNew || $updateEditor)){
			$return = $this->fpdo
				->update('publication_person', $data, $record)
				->execute() === 1;
		} elseif($createNew){
			$this->fpdo
				->update('publication_person', ['publication_version_max'=>$version-1],$record)
				->execute();
			$return = $this->insertPerson(array_merge($data, ['publication_version_min'=>$version]));
		} elseif($updateEditor) {
			$return = $this->fpdo
				->update('publication_person', ['editor'=>$data['editor']], $record)
				->execute();
		} else {
			$return = true;
		}
		return $return === true ? $record : $return;
	}
	
	public function deletePerson($publication_id, $version, $currentPersons){
		$q = $this->fpdo
			->update('publication_person')
			->set('publication_version_max', $version)
			->where('publication_id', $publication_id)
			->where('publication_version_max', null);
		if(count($currentPersons) > 0){
			var_dump($currentPersons);
			foreach($currentPersons as $person){
				if(!is_numeric($person)){
					die('Hacking attempt!');
				}
			}
			if(count($currentPersons) === 1){
				$q->where('publication_person_id <> ?',$currentPersons[0]);
			} else {
				$q->where('publication_person_id NOT IN ('.implode(',',$currentPersons).')');
			}
		}
		$q->execute();
		$this->fpdo
			->deleteFrom('publication_person')
			->where('publication_version_max < publication_version_min')
			->execute();
		return true;
	}

	public function insertKeyword($word, $id, $version){
		return $this->fpdo
			->insertInto('publication_keyword')
			->values(['publication_id'=>$id,
				'publication_version_min'=>$version,
				'keyword'=>$word])
			->execute();
	}
	
	public function deleteKeyword($word, $id, $version){
		$this->fpdo
			->update('publication_keyword')
			->set('publication_version_max', $version)
			->where('publication_id', $id)
			->where('keyword', $word)
			->where('publication_version_max', null)
			->execute();
		$this->fpdo
			->deleteFrom('publication_keyword')
			->where('publication_version_max < publication_version_min')
			->execute();
	}
	
	public function insertProject($data){
		$this->fpdo
			->insertInto('project_publication')
			->values($data)
			->execute();
	}
	public function deleteProject($publication_id, $version, $currentProjects){
		$q = $this->fpdo
			->update('project_publication')
			->set('publication_version_max', $version)
			->where('publication_id', $publication_id)
			->where('publication_version_max', null);
		if(count($currentProjects) > 0){
			if(count($currentProjects) === 1){
				$q->where('project_id <> ?',$currentProjects[0]);
			} else {
				$q->where('project_id NOT', $currentProjects);
			}
		}
		$q->execute();
		/*\npdc\lib\Db::executeQuery('UPDATE project_publication SET project_version_max=project_version FROM project WHERE publication_version_max IS NOT NULL AND project.project_id=project_publication.project_id AND record_status=\'published\'');		
		\npdc\lib\Db::executeQuery('UPDATE project_publication SET publication_version_max=publication_version FROM publication WHERE project_version_max IS NOT NULL AND publication.publication_id=project_publication.publication_id AND record_status=\'published\'');
		$this->fpdo
			->deleteFrom('project_publication')
			->where('publication_version_max < publication_version_min')
			->execute();*/
		return true;
	}
	
		public function insertDataset($data){
		$this->fpdo
			->insertInto('dataset_publication')
			->values($data)
			->execute();
	}
	public function deleteDataset($publication_id, $version, $currentDatasets){
		$q = $this->fpdo
			->update('dataset_publication')
			->set('publication_version_max', $version)
			->where('publication_id', $publication_id)
			->where('publication_version_max', null);
		if(count($currentDatasets) > 0){
			if(count($currentDatasets) === 1){
				$q->where('dataset_id <> ?',$currentDatasets[0]);
			} else {
				$q->where('dataset_id NOT', $currentDatasets);
			}
		}
		$q->execute();
		/*\npdc\lib\Db::executeQuery('UPDATE dataset_publication SET dataset_version_max=dataset_version FROM dataset WHERE publication_version_max IS NOT NULL AND dataset.dataset_id=dataset_publication.dataset_id AND record_status=\'published\'');		
		\npdc\lib\Db::executeQuery('UPDATE dataset_publication SET publication_version_max=publication_version FROM publication WHERE dataset_version_max IS NOT NULL AND publication.publication_id=dataset_publication.publication_id AND record_status=\'published\'');
		$this->fpdo
			->deleteFrom('dataset_publication')
			->where('publication_version_max < publication_version_min')
			->execute();*/
		return true;
	}
	
	public function setStatus($publication_id, $old, $new, $comment = null){
		$r = $this->fpdo->from('publication')
			->where('publication_id', $publication_id)
			->where('record_status', $old)
			->fetch();
		if($r !== false){
			$q = $this->fpdo
				->update('publication')
				->set('record_status', $new);
			if($new === 'published'){
				$q->set('published', date("Y-m-d H:i:s", time()));
			}
			$return = $q
				->where('publication_id', $publication_id)
				->where('record_status', $old)
				->execute();
			$this->fpdo->insertInto('record_status_change', ['publication_id'=>$publication_id, 'version'=>$r['publication_version'], 'old_state'=>$old, 'new_state'=>$new, 'person_id'=>$_SESSION['user']['id'], 'comment'=>$comment])->execute();
		}
		return $return;
	}	
}