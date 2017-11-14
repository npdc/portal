<?php

/**
 * get person data
 */
namespace npdc\model;


class Person{
	private $fpdo;

	/**
	 * constructor
	 */
	public function __construct(){
		$this->fpdo = \npdc\lib\Db::getFPDO();
	}
	
	public function getList($filters){
		$q = $this->fpdo
			->from('person')
			->leftJoin('organization USING(organization_id)')->select('organization.*')
			->orderBy('name');
		if(!is_null($filters)){
			foreach($filters as $filter=>$values){
				if(
						(is_array($values) && count($values) === 0)
						|| is_null($values) 
						|| $values === ''
					){
					continue;
				}
				switch($filter){
					case 'hint':
					case 'submit':
						break;

					case 'organization':
						$q->where('organization_id IN ('.implode(',', $values).')');
						break;
					case 'type':
						foreach($values as $value){
							$q->where('person_id IN (SELECT person_id FROM '.$value.'_person)');
						}
				}
			}
		}
		return $q->fetchAll();
	}

	/**
	 * get a person by id
	 * @param integer $id person id
	 * @return object person
	 */
	public function getById($id){
		if($id === null){ return null;}
		$data = $this->fpdo
			->from('person', $id)
			->leftJoin('organization')->select('organization.*')
			->fetch();
		if(!empty($data['orcid'])){
			$data['orcid'] = implode('-', str_split($data['orcid'], 4));
		}
		foreach($data as $key=>&$value){
			if(preg_match('/^phone_[a-z]{1,}_public$/', $key)){
				$value = $value === 1 ? 'yes' : 'no';
			}
		}
		unset($value);
		return $data;
	}
	
	public function getUserLevels(){
		return $this->fpdo->from('user_level')->orderBY('user_level_id')->fetchAll();
	}
	
	public function getUserLevelDetails($level){
		$q = $this->fpdo
			->from('user_level');
		if(is_numeric($level)){
			$q->where('user_level_id <= ?', $level);
		} else {
			$q->where('user_level_id <= (SELECT user_level_id FROM user_level WHERE label=?)', $level);
		}
		$rows = $q->orderBy('user_level_id')
			->fetchAll();
		$description = '';
		foreach($rows as $row){
			$description .= $row['description']."\r\n";
		}
		$description = '<ul>'. str_replace(['- ', "\r\n"], ['<li>', '</li>'], $description).'</ul>';
		return ['name'=>$row['name'], 'description'=>$description];
	}
	
	public function getByMail($mail){
		return $this->fpdo
			->from('person')
			->where('mail', $mail)
			->fetchAll();
	}
	/**
	 * get a user by its mail address
	 * @param string $mail mail address of user
	 * @return object person
	 */
	public function getUser($mail){
		return $this->fpdo
			->from('person')
			->where('mail', $mail)
			->where('password IS NOT NULL')
			->where('user_level IS NOT NULL')
			->fetch();
	}
	
	public function requestPassword($data){
		$this->fpdo->update('account_new')->set(['expire_reason'=>'new link'])->where($data)->where('expire_reason IS NULL')->execute();
		$code = bin2hex(random_bytes(16));
		$data['code'] = password_hash($code, PASSWORD_DEFAULT);
		$id = \npdc\lib\Db::insertReturnId('account_new', $data);
		return [$id, $code];
	}

	public function getPasswordNew($id){
		return $this->fpdo
			->from('account_new')
			->where('account_new_id', $id)
			->where('used_time IS NULL')
			->where('expire_reason IS NULL')
			->where('request_time > NOW() - INTERVAL '.(\npdc\config::$db['type']==='pgsql' ? '\''.\npdc\config::$resetExpiryHours.' hours\'' : \npdc\config::$resetExpiryHours.' HOUR'))
			->fetch();
	}
	
	public function usePasswordNew($id){
		$this->fpdo->update('account_new')
			->set(['used_time'=>date('Y-m-d h:i:s'), 'expire_reason'=>'Used'])
			->where('account_new_id', $id)
			->execute();
	}
	
	public function requestPasswordReset($data){
		$this->fpdo
			->update('account_reset')
			->set(['expire_reason'=>'new link'])
			->where($data)
			->where('expire_reason IS NULL')
			->where('request_time > NOW() - INTERVAL '.(\npdc\config::$db['type']==='pgsql' ? '\''.\npdc\config::$resetExpiryHours.' hours\'' : \npdc\config::$resetExpiryHours.' HOUR'))
			->execute();
		$code = bin2hex(random_bytes(16));
		$data['code'] = password_hash($code, PASSWORD_DEFAULT);
		$this->fpdo->insertInto('account_reset', $data)->execute();
		if(count($this->fpdo->from('account_reset')->where($data)->fetchAll()) > 0){
			return $code;
		}
	}
	
	public function getPasswordReset($person_id){
		return $this->fpdo
			->from('account_reset')
			->where('person_id', $person_id)
			->where('used_time IS NULL')
			->where('expire_reason IS NULL')
			->where('request_time > NOW() - INTERVAL '.(\npdc\config::$db['type']==='pgsql' ? '\''.\npdc\config::$resetExpiryHours.' hours\'' : \npdc\config::$resetExpiryHours.' HOUR'))
			->fetch();
	}
	
	public function expirePasswordResetLogin($person_id){
		$this->fpdo->update('account_reset')
			->set(['expire_reason'=>'User logged in'])
			->where('person_id', $person_id)
			->where('expire_reason IS NULL')
			->where('request_time > NOW() - INTERVAL '.(\npdc\config::$db['type']==='pgsql' ? '\''.\npdc\config::$resetExpiryHours.' hours\'' : \npdc\config::$resetExpiryHours.' HOUR'))
			->execute();
	}

	public function usePasswordReset($account_reset_id){
		$this->fpdo->update('account_reset')
			->set(['used_time'=>date('Y-m-d h:i:s'), 'expire_reason'=>'Used'])
			->where('account_reset_id', $account_reset_id)
			->execute();
	}

	/**
	 * get projects of person
	 * @param integer $id person id
	 * @return array list of projects
	 */
	public function getProjects($id, $published = true){
		$q = $this->fpdo
			->from('project_person')
			->join('project')->select('project.*')
			->where('person_id', $id)
			->where('project.project_version>=project_version_min')
			->where('(project_version_max IS NULL OR project_version_max >= project.project_version)')
			->orderBy('date_start DESC, date_end DESC');
		if($published){
			$q->where('record_status', 'published');
		} else {
			$q->join('(SELECT project_id, MAX(project_version) project_version FROM project GROUP BY project_id) AS a ON a.project_id=project.project_id AND a.project_version=project.project_version');
		}
		
		return $q->fetchAll();
	}
	
	/**
	 * get publications of person
	 * @param integer $id person id
	 * @return array list of publications
	 */
	public function getPublications($id, $published = true){
		$q = $this->fpdo
			->from('publication_person')
			->join('publication')->select('publication.*, EXTRACT(YEAR FROM date) AS year')
			->where('person_id', $id)
			->where('publication.publication_version>=publication_version_min')
			->where('(publication_version_max IS NULL OR publication_version_max >= publication.publication_version)')
			->orderBy('date DESC');
		if($published){
			$q->where('record_status', 'published');
		} else {
			$q->join('(SELECT publication_id, MAX(publication_version) publication_version FROM publication GROUP BY publication_id) AS a ON a.publication_id=publication.publication_id AND a.publication_version=publication.publication_version');
		}
		return $q->fetchAll();
	}
	
	/**
	 * get datasets of person
	 * @param integer $id person id
	 * @return array list of datasets
	 */
	public function getDatasets($id, $published = true){
		$q = $this->fpdo
			->from('dataset_person')
			->join('dataset')->select('dataset.*')
			->where('person_id', $id)
			->where('dataset.dataset_version>=dataset_version_min')
			->where('(dataset_version_max IS NULL OR dataset_version_max >= dataset.dataset_version)')
			->orderBy('date_start DESC');
		if($published){
			$q->where('record_status', 'published');
		} else {
			$q->join('(SELECT dataset_id, MAX(dataset_version) dataset_version FROM dataset GROUP BY dataset_id) AS a ON a.dataset_id=dataset.dataset_id AND a.dataset_version=dataset.dataset_version');
		}
		return $q->fetchAll();
	}
	
	public function search($string, $exclude, $fuzzy = false){
		$query = $this->fpdo
			->from('person');
		if(strlen($string) > 0){
			if($fuzzy){
				if(strpos($string, ',') !== false){
					$string = implode(' ', array_reverse(explode(',', $string)));
				}
				if(strpos($string, '.') !== false){
					$string = substr($string, strrpos($string, '.')+1);
				} elseif(strpos($string, ' ') !== false) {
					$string = substr($string, strpos($string, ' '));
				}
				$string = trim($string);
				$query->where('levenshtein_ratio(?, surname) >= '.\npdc\config::$levenshtein_ratio_person, $string);
				$query->orderBy('levenshtein_ratio(?, surname) DESC', $string);
			} else {
				$query->where('name REGEXP ?', $string);
			}
		}
		$query->orderBy('name');
		if(is_array($exclude) && count($exclude) > 0){
			foreach($exclude as $id){
				if(!is_numeric($id)){
					die('Hacking attempt');
				}
			}
			$query->where('person_id NOT', $exclude);
		}
		return $query->fetchAll();
	}
	
	public function checkMail($mail, $ownId){
		return $this->fpdo
			->from('person')
			->where('mail = ?', $mail)
			->where('person_id <> ?', $ownId)
			->count() === 0;
	}
	
	public function insertPerson($data){
		$data = $this->parseData($data);
		$this->fpdo
			->insertInto('person')
			->values($data)
			->execute();
		return $this->fpdo
			->from('person')
			->where($data)
			->orderBy('person_id')
			->fetch()['person_id'];
	}

	public function updatePerson($data, $id){
		return $this->fpdo
			->update('person', $this->parseData($data), $id)
			->execute();
	}
	
	private function parseData($data){
		if(!empty($data['orcid'])){
			$data['orcid'] = str_replace('-', '', $data['orcid']);
		}
		foreach($data as $key=>&$value){
			if(preg_match('/^phone_[a-z]{1,}_public$/', $key)){
				$value = $value === 'yes' ? 1 : 0;
			}
		}
		unset($value);
		return $data;
	}
}
