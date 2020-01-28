<?php

namespace npdc\model;

abstract class Base {
	protected $baseTbl;
	protected $dsql;

	/**
	 * Constructor
	 */
	public function __construct(){
		$this->dsql = \npdc\lib\Db::getDSQLcon();
	}

	protected function _updateSub($tbl, $record, $data, $version){
		$oldRecord = \npdc\lib\Db::get($tbl, $record);
		$createnew = false;
		if($oldRecord[$this->baseTbl.'_version_min'] != $version){
			foreach($data as $key=>$val){
				if($val != $oldRecord[$key]){
					$createNew = true;
				}
			}
		}
		if($createNew){
			\npdc\lib\Db::update($tbl, $record, [$this->baseTbl.'_version_max'=>$version-1]);
			$data[$this->baseTbl.'_version_min'] = $version;
			return \npdc\lib\Db::insert($tbl, $data, true);
		} else {
			return \npdc\lib\Db::update($tbl, $record, $data);
		}
	}

	protected function _deleteSub($tbl, $id, $version, $current, $parent = null){
		if(is_null($parent)){
			$parent = $this->baseTbl;
		}
		$q = $this->dsql->dsql()
			->table($tbl)
			->where($parent.'_id', $id)
			->where($this->baseTbl.'_version_max IS NULL');
		if(!empty($current) && !(count($current) === 1 && empty($current[0]))){
			$q->where($tbl.'_id', 'NOT', $current);
		}
		$q->set($this->baseTbl.'_version_max', $version)
			->update();
		$this->dsql->dsql()
			->table($tbl)
			->where($this->dsql->expr($this->baseTbl.'_version_min > '.$this->baseTbl.'_version_max'))
			->delete();
	}

	public function updatePerson($record, $data, $version){
		$oldRecord = \npdc\lib\Db::get($this->baseTbl.'_person', $record);
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
		if($oldRecord[$this->baseTbl.'_version_min'] === $version && ($createNew || $updateEditor)){
			$return = \npdc\lib\Db::update($this->baseTbl.'_person', $record, $data);
		} elseif($createNew){
			\npdc\lib\Db::update($this->baseTbl.'_person', $record, [$this->baseTbl.'_version_max'=>$version-1]);
			$return = $this->insertPerson(array_merge($data, $record, [$this->baseTbl.'_version_min'=>$version, $this->baseTbl.'_id'=>$oldRecord[$this->baseTbl.'_id']]));
		} elseif($updateEditor) {
			$return = \npdc\lib\Db::update($this->baseTbl.'_person', $record,['editor'=>$data['editor']]);
		} else {
			$return = true;
		}
		return $return;
	}

	public function setStatus($id, $old, $new, $comment = null){
		$r = \npdc\lib\Db::get($this->baseTbl, [$this->baseTbl.'_id'=>$id, 'record_status'=>$old]);
		if($r !== false){
			$q = $this->dsql->dsql()
				->table($this->baseTbl)
				->where($this->baseTbl.'_id', $id)
				->where('record_status', $old)
				->set('record_status', $new);
			if($new === 'published'){
				$q->set('published', date("Y-m-d H:i:s", time()));
			}
			$return = $q->update();
			\npdc\lib\Db::insert('record_status_change', [$this->baseTbl.'_id'=>$id, 'version'=>$r[$this->baseTbl.'_version'], 'old_state'=>$old, 'new_state'=>$new, 'person_id'=>$_SESSION['user']['id'], 'comment'=>$comment]);
		}
		return $return;
	}

	/**
	 * Get last status change of the version
	 *
	 * @param integer $id id
	 * @param integer $version version
	 * @param string|null $state new state to look for
	 * @return array status change with person
	 */
	public function getLastStatusChange($id, $version, $state = null){
		$q = $this->dsql->dsql()
			->table('record_status_change')
			->join('person.person_id', 'person_id', 'inner')
			->where($this->baseTbl.'_id', $id)
			->where('version', $version)
			->order('datetime DESC');
		if($state !== null){
			$q->where('new_state', $state);
		}
		return $q->get()[0];
	}
	
	/**
	 * Get list of status changes of the version
	 *
	 * @param integer $id id
	 * @param integer $version dataset version
	 * @return array list of status changes with persons details
	 */
	public function getStatusChanges($id, $version){
		return $this->dsql->dsql()
			->table('record_status_change')
			->join('person.person_id', 'person_id', 'inner')
			->where($this->baseTbl.'_id', $id)
			->where('version', $version)
			->order('datetime DESC')
			->get();
	}
	
	/**
	 * Check if person is allowed to edit record
	 *
	 * @param integer $id id of record
	 * @param integer $person_id id of person
	 * @return boolean user is allowed to edit
	 */
	public function isEditor($id, $person_id){
		return is_numeric($id) && is_numeric($person_id)
			? (
				count($this->dsql->dsql()
				->table($this->baseTbl.'_person')
				->where($this->baseTbl.'_id', $id)
				->where('person_id', $person_id)
				->where($this->baseTbl.'_version_max IS NULL')
				->where('editor')
				->get()) > 0
			) || (
				count($this->dsql->dsql()
				->table($this->baseTbl)
				->where($this->baseTbl.'_id', $id)
				->where('creator', $person_id)
				->where('record_status IN (\'draft\', \'published\')')
				->get()) > 0
			)
			: false;
	}

	/**
	 * Get all available versions of record
	 *
	 * @param integer $id record id
	 * @return array list of available versions with status of each version
	 */
	
	public function getVersions($id){
		return $this->dsql->dsql()
			->table($this->baseTbl)
			->where($this->baseTbl.'_id', $id)
			->field($this->baseTbl.'_version, record_status, uuid')
			->order($this->baseTbl.'_version DESC')
			->get();
	}

	protected abstract function parseGeneral($data, $action);

	public function insertGeneral($data){
		$values = $this->parseGeneral($data, 'insert');
		$id = \npdc\lib\Db::insert($this->baseTbl, $values, true);
		$uuid = \Lootils\Uuid\Uuid::createV5(
			\npdc\config::$UUIDNamespace ?? \Lootils\Uuid\Uuid::createV4(),
			$this->baseTbl.'/'.$id.'/'.$values[$this->baseTbl.'_version']
		)->getUUID();
		\npdc\lib\Db::update($this->baseTbl, [$this->baseTbl.'_id'=>$id, $this->baseTbl.'_version'=>$values[$this->baseTbl.'_version']], ['uuid'=>$uuid]);
		return $id;
	}
	
	public function updateGeneral($data, $id, $version){
		\npdc\lib\Db::update($this->baseTbl,
			[
				$this->baseTbl.'_id'=>$id,
				$this->baseTbl.'_version'=>$version
			],
			$this->parseGeneral($data, 'update')
		);
	}

	public function insertPerson($data){
		return \npdc\lib\Db::insert($this->baseTbl.'_person', $data);
	}
	
	public function deletePerson($id, $version, $currentPersons){
		$q = $this->dsql->dsql()
			->table($this->baseTbl.'_person')
			->where($this->baseTbl.'_id', $id)
			->where($this->baseTbl.'_version_max IS NULL');
		if(count($currentPersons) > 0){
			$q->where('person_id', 'NOT', $currentPersons);
		}
		$q->set($this->baseTbl.'_version_max', $version)
			->update();
	}

	public function insertKeyword($word, $id, $version){
		return \npdc\lib\Db::insert($this->baseTbl.'_keyword',
			[
				$this->baseTbl.'_id'=>$id,
				$this->baseTbl.'_version_min'=>$version,
				'keyword'=>$word
			],
			true
		);
	}
	
	public function deleteKeyword($word, $id, $version){
		$this->dsql->dsql()
			->table($this->baseTbl.'_keyword')
			->where($this->baseTbl.'_id', $id)
			->where('keyword', $word)
			->where($this->baseTbl.'_version_max IS NULL')
			->set($this->baseTbl.'_version_max', $version)
			->update();
		$this->dsql->dsql()
			->table($this->baseTbl.'_keyword')
			->where($this->baseTbl.'_version_max < '.$this->baseTbl.'_version_min')
			->delete();
	}
}