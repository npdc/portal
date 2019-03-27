<?php

/**
 * Person model
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */
namespace npdc\model;

/**
 * Get person data
 */
class Person{
	private $fpdo;

	/**
	 * constructor
	 */
	public function __construct(){
		$this->fpdo = \npdc\lib\Db::getFPDO();
		$this->dsql = \npdc\lib\Db::getDSQLcon();
	}
	
	/**
	 * Get list of persons, filtered by $filters
	 *
	 * @param array $filters filters 
	 * @return array list of persons
	 */
	public function getList($filters){
		//var_dump($filters);die();
		$q = $this->dsql->dsql()->table('person')
			->join('organization.organization_id', 'organization_id', 'left')
			->order('name')->field('person.*, organization_name');
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
						$q->where('organization_id', $values);
						break;
					case 'type':
						foreach($values as $value){
							$q->where($q->expr('person_id IN (SELECT person_id FROM '.$value.'_person)'));
						}
						break;
					case 'userLevel':
						$q->where('user_level', $values);
					case 'hasPassword':
						if($values[0] === 'yes'){
							$q->where('password IS NOT NULL');
						}
						break;
				}
			}
		}
		return $q->get();
	}

	/**
	 * get a person by id
	 * 
	 * @param integer $id person id
	 * @return array person
	 */
	public function getById($id){
		if($id === null){ return null;}
		$data = $this->fpdo
			->from('person', $id)
			->leftJoin('organization')->select('organization.*')
			->fetch();
		if($data !== false){
			if(!empty($data['orcid'])){
				$data['orcid'] = implode('-', str_split($data['orcid'], 4));
			}
			foreach($data as $key=>&$value){
				if(preg_match('/^phone_[a-z]{1,}_public$/', $key)){
					$value = $value === 1 ? 'yes' : 'no';
				}
			}
			unset($value);
		}
		return $data;
	}
	
	/**
	 * Get all available user levels
	 *
	 * @return array levels
	 */
	public function getUserLevels(){
		return $this->fpdo->from('user_level')->orderBy('user_level_id')->fetchAll();
	}
	
	/**
	 * Get details of user level
	 *
	 * @param string $level user level name
	 * @return array details
	 */
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
	
	/**
	 * Get person(s) by e-mail address
	 *
	 * @param string $mail e-mail address
	 * @return array persons
	 */
	public function getByMail($mail){
		return $this->fpdo
			->from('person')
			->where('mail', $mail)
			->fetchAll();
	}

	/**
	 * get a user by its e-mail address
	 * 
	 * @param string $mail e-mail address of user
	 * @return array person
	 */
	public function getUser($mail){
		return $this->fpdo
			->from('person')
			->where('mail', $mail)
			->where('password IS NOT NULL')
			->where('user_level IS NOT NULL')
			->fetch();
	}
	
	/**
	 * Create new account request
	 *
	 * @param array $data
	 * @return array id and request code
	 */
	public function requestPassword($data){
		$this->fpdo->update('account_new')->set(['expire_reason'=>'new link'])->where($data)->where('expire_reason IS NULL')->execute();
		$code = bin2hex(random_bytes(16));
		$data['code'] = password_hash($code, PASSWORD_DEFAULT);
		$id = \npdc\lib\Db::insertReturnId('account_new', $data);
		return [$id, $code];
	}

	/**
	 * Retreive new account request
	 *
	 * @param integer $id
	 * @return object request
	 */
	public function getPasswordNew($id){
		return $this->fpdo
			->from('account_new')
			->where('account_new_id', $id)
			->where('used_time IS NULL')
			->where('expire_reason IS NULL')
			->where('request_time > NOW() - INTERVAL '.(\npdc\config::$db['type']==='pgsql' ? '\''.\npdc\config::$resetExpiryHours.' hours\'' : \npdc\config::$resetExpiryHours.' HOUR'))
			->fetch();
	}
	
	/**
	 * Marks new account request as used
	 *
	 * @param integer $id
	 * @return void
	 */
	public function usePasswordNew($id){
		$this->fpdo->update('account_new')
			->set(['used_time'=>date('Y-m-d h:i:s'), 'expire_reason'=>'Used'])
			->where('account_new_id', $id)
			->execute();
	}

	/**
	 * Store request password reset link
	 *
	 * @param array $data
	 * @return string reset code
	 */
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
	
	/**
	 * Retreive password reset code
	 *
	 * @param integer $person_id
	 * @return object person
	 */
	public function getPasswordReset($person_id){
		return $this->fpdo
			->from('account_reset')
			->where('person_id', $person_id)
			->where('used_time IS NULL')
			->where('expire_reason IS NULL')
			->where('request_time > NOW() - INTERVAL '.(\npdc\config::$db['type']==='pgsql' ? '\''.\npdc\config::$resetExpiryHours.' hours\'' : \npdc\config::$resetExpiryHours.' HOUR'))
			->fetch();
	}
	
	/**
	 * Mark password reset code as expired beacause of user login
	 *
	 * @param integer $person_id
	 * @return void
	 */
	public function expirePasswordResetLogin($person_id){
		$this->fpdo->update('account_reset')
			->set(['expire_reason'=>'User logged in'])
			->where('person_id', $person_id)
			->where('expire_reason IS NULL')
			->where('request_time > NOW() - INTERVAL '.(\npdc\config::$db['type']==='pgsql' ? '\''.\npdc\config::$resetExpiryHours.' hours\'' : \npdc\config::$resetExpiryHours.' HOUR'))
			->execute();
	}

	/**
	 * Mark password reset code as used
	 *
	 * @param integer $account_reset_id
	 * @return void
	 */
	public function usePasswordReset($account_reset_id){
		$this->fpdo->update('account_reset')
			->set(['used_time'=>date('Y-m-d h:i:s'), 'expire_reason'=>'Used'])
			->where('account_reset_id', $account_reset_id)
			->execute();
	}

	/**
	 * get projects of person
	 * 
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
	 * 
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
	 * 
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
	
	/**
	 * Search person
	 *
	 * @param string $string string to search for
	 * @param array $exclude id's of persons to exclude
	 * @param boolean $fuzzy whether fuzzy search has to be used
	 * @return array resulting people
	 */
	public function search($string, $exclude, $fuzzy = false){
		$query = $this->fpdo
			->from('person');
		if(strlen($string) > 0){
			if($fuzzy){
				if(strpos($string, ',') !== false){
					$string = implode(' ', array_reverse(explode(',', $string)));
				}
				preg_match(\npdc\config::$surname_regex, $string, $parts);
				$query->where('levenshtein_ratio(?, surname) >= '.\npdc\config::$levenshtein_ratio_person, $parts['f']);
				if($parts['f'] !== $parts['l']){
					$subs = substr($string, strrpos($string, ' ')+1);
					if(\npdc\config::$db['type'] === 'mysql'){
						$query->where('levenshtein_ratio(?, SUBSTRING_INDEX(surname, \' \', -1)) >= '.\npdc\config::$levenshtein_ratio_person, $parts['l']);
					}
					//$query->where('levenshtein_ratio(?, regexp_replace(surname, \'^.*\', \'\')) >= '.\npdc\config::$levenshtein_ratio_person, $parts['l']);
				}
				$query->orderBy('levenshtein_ratio(?, surname) DESC', $string);
			} else {
				$query->where('name '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' ?', $string);
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
	
	/**
	 * Check if there is no person yet with the specified mail address
	 *
	 * @param string $mail
	 * @param integer $ownId id of person being changed
	 * @return boolean
	 */
	public function checkMail($mail, $ownId){
		return $this->fpdo
			->from('person')
			->where('mail = ?', $mail)
			->where('person_id <> ?', $ownId)
			->count() === 0;
	}
	
	/**
	 * Add a person to the database
	 *
	 * @param array $data
	 * @return integer id of newly inserted person
	 */
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

	/**
	 * Update person record
	 *
	 * @param array $data new data of person
	 * @param integer $person_id if of record to be updated
	 * @return void
	 */
	public function updatePerson($data, $person_id){
		return $this->fpdo
			->update('person', $this->parseData($data), $person_id)
			->execute();
	}
	
	/**
	 * Reformat person data for storage in database
	 *
	 * @param array $data
	 * @return array
	 */
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
