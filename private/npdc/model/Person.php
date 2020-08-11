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
	private $dsql;

	/**
	 * Constructor
	 */
	public function __construct(){
		$this->dsql = \npdc\lib\Db::getDSQLcon();
	}
	
	/**
	 * Get list of persons, filtered by $filters
	 *
	 * @param array $filters filters 
	 * @return array list of persons
	 */
	public function getList($filters){
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
							$q->where(
								$q->expr(
									'person_id IN (SELECT person_id FROM '
									.$value.'_person)')
								);
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
		$data = $this->dsql->dsql()
			->table('person')
			->join('organization.organization_id', 'organization_id', 'left')
			->where('person_id', $id)
			->get()[0];
		if(!empty($data)){
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
		return $this->dsql->dsql()
			->table('user_level')
			->order('user_level_id')
			->get();
	}
	
	/**
	 * Get details of user level
	 *
	 * @param string $level user level name
	 * @return array details
	 */
	public function getUserLevelDetails($level){
		$q = $this->dsql->dsql()
			->table('user_level');
		if(is_numeric($level)){
			$q->where('user_level_id', '<=', $level);
		} else {
			$q->where('user_level_id', '<=', 
				$q->dsql()->table('user_level')
					->field('user_level_id')
					->where('label', $level)
			);
		}
		$rows = $q->order('user_level_id')
			->get();
		$description = '';
		foreach($rows as $row){
			$description .= $row['description']."\r\n";
		}
		$description = '<ul>'
			.str_replace(['- ', "\r\n"], ['<li>', '</li>'], $description)
			.'</ul>';
		return ['name'=>$row['name'], 'description'=>$description];
	}
	
	/**
	 * Get person(s) by e-mail address
	 *
	 * @param string $mail e-mail address
	 * @return array persons
	 */
	public function getByMail($mail){
		return $this->dsql->dsql()
			->table('person')
			->where('mail', $mail)
			->get();
	}

	/**
	 * get a user by its e-mail address
	 * 
	 * @param string $mail e-mail address of user
	 * @return array person
	 */
	public function getUser($mail){
		$q = $this->dsql->dsql()
			->table('person')
			->where('mail', $mail);
		$q->where($q->expr('password IS NOT NULL'))
			->where($q->expr('user_level IS NOT NULL'));
		return $q->get()[0];
	}
	
	/**
	 * Create new account request
	 *
	 * @param array $data
	 * @return array id and request code
	 */
	public function requestPassword($data){
		$q = $this->dsql->dsql()
			->table('account_new')
			->where('expire_reason IS NULL');
		foreach($data as $key=>$val){
			$q->where($key, $val);
		}
		$q->set('expire_reason', 'new link')->update();
		$code = bin2hex(random_bytes(16));
		$data['code'] = password_hash($code, PASSWORD_DEFAULT);
		$id = \npdc\lib\Db::insert('account_new', $data, true);
		return [$id, $code];
	}

	/**
	 * Retreive new account request
	 *
	 * @param integer $id
	 * @return object request
	 */
	public function getPasswordNew($id){
		$q = $this->dsql->dsql()
			->table('account_new')
			->where('account_new_id', $id);
		return $q->where($q->expr('used_time IS NULL'))
			->where($q->expr('expire_reason IS NULL'))
			->where(
				$q->expr('request_time > NOW() - INTERVAL '
					.(\npdc\config::$db['type']==='pgsql' 
						? '\''.\npdc\config::$resetExpiryHours.' hours\'' 
						: \npdc\config::$resetExpiryHours.' HOUR'
					)
				)
			)
			->get()[0];
	}
	
	/**
	 * Marks new account request as used
	 *
	 * @param integer $id
	 * @return void
	 */
	public function usePasswordNew($id){
		return $this->dsql->dsql()->table('account_new')
			->set(['used_time'=>date('Y-m-d h:i:s'), 'expire_reason'=>'Used'])
			->where('account_new_id', $id)
			->update();
	}

	/**
	 * Store request password reset link
	 *
	 * @param array $data
	 * @return string reset code
	 */
	public function requestPasswordReset($data){
		$q = $this->dsql->dsql()
			->table('account_reset')
			->where('expire_reason IS NULL');
			foreach($data as $key=>$val){
				$q->where($key, $val);
			}
		$q->set('expire_reason', 'new link')->update();
		$code = bin2hex(random_bytes(16));
		$data['code'] = password_hash($code, PASSWORD_DEFAULT);
		if(is_numeric(\npdc\lib\Db::insert('account_reset', $data, true))){
			return $code;
		} else {
			return false;
		}
	}
	
	/**
	 * Retreive password reset code
	 *
	 * @param integer $person_id
	 * @return object person
	 */
	public function getPasswordReset($person_id){
		$q = $this->dsql->dsql()
			->table('account_reset')
			->where('person_id', $person_id);
		return $q->where($q->expr('used_time IS NULL'))
			->where($q->expr('expire_reason IS NULL'))
			->where(
				$q->expr('request_time > NOW() - INTERVAL '
					.(\npdc\config::$db['type']==='pgsql' 
						? '\''.\npdc\config::$resetExpiryHours.' hours\'' 
						: \npdc\config::$resetExpiryHours.' HOUR'
					)
				)
			)->get()[0];
	}
	
	/**
	 * Mark password reset code as expired beacause of user login
	 *
	 * @param integer $person_id
	 * @return void
	 */
	public function expirePasswordResetLogin($person_id){
		$q = $this->dsql->dsql()
			->table('account_reset')
			->where('person_id', $person_id);
		$q->where($q->expr('used_time IS NULL'))
			->where($q->expr('expire_reason IS NULL'))
			->where(
				$q->expr('request_time > NOW() - INTERVAL '
					.(\npdc\config::$db['type']==='pgsql' 
						? '\''.\npdc\config::$resetExpiryHours.' hours\'' 
						: \npdc\config::$resetExpiryHours.' HOUR'
					)
				)
			)
			->set(['expire_reason'=>'User logged in'])
			->update();
	}

	/**
	 * Mark password reset code as used
	 *
	 * @param integer $account_reset_id
	 * @return void
	 */
	public function usePasswordReset($account_reset_id){
		$this->dsql->dsql()
			->table('account_reset')
			->where('account_reset_id', $account_reset_id)
			->set(['used_time'=>date('Y-m-d h:i:s'), 'expire_reason'=>'Used'])
			->update();
	}

	/**
	 * get projects of person
	 * 
	 * @param integer $id person id
	 * @return array list of projects
	 */
	public function getProjects($id){
		return $this->dsql->dsql()
			->table('project_person')
			->join('project',
				\npdc\lib\Db::joinVersion('project', 'project_person'),
				'inner')
			->where('person_id', $id)
			->where('record_status', 'published')
			->order('date_start DESC, date_end DESC')
			->get();
	}
	
	/**
	 * get publications of person
	 * 
	 * @param integer $id person id
	 * @return array list of publications
	 */
	public function getPublications($id){
		return $this->dsql->dsql()
			->table('publication_person')
			->join('publication',
				\npdc\lib\Db::joinVersion('publication', 'publication_person'),
				'inner')
			->where('person_id', $id)
			->where('record_status', 'published')
			->order('date DESC')
			->get();
	}
	
	/**
	 * get datasets of person
	 * 
	 * @param integer $id person id
	 * @return array list of datasets
	 */
	public function getDatasets($id, $published = true){
		return $this->dsql->dsql()
			->table('dataset_person')
			->join('dataset',
				\npdc\lib\Db::joinVersion('dataset', 'dataset_person'),
				'inner')
			->where('person_id', $id)
			->where('record_status', 'published')
			->order('date_start DESC')
			->get();
	}
	
	/**
	 * Search person
	 *
	 * @param string $string string to search for
	 * @param array $exclude id's of persons to exclude
	 * @param boolean $fuzzy whether fuzzy search has to be used
	 * @return array resulting people
	 */
	public function search($string, $exclude = [], $fuzzy = false){
		$q = $this->dsql->dsql()
			->table('person');
		if(strlen($string) > 0){
			if($fuzzy){
				if(strpos($string, ',') !== false){
					$string = implode(' ',
						array_reverse(explode(',', $string)));
				}
				preg_match(\npdc\config::$surname_regex, $string, $parts);
				$q->where(
					$q->expr('levenshtein_ratio([], surname) >= []',
						[$parts['f'],
						\npdc\config::$levenshtein_ratio_person]
					)
				);
				if($parts['f'] !== $parts['l']){
					$subs = substr($string, strrpos($string, ' ')+1);
					if(\npdc\config::$db['type'] === 'mysql'){
						$q->where(
							$q->expr('levenshtein_ratio([], surname) >= []',
								[$parts['l'],
								\npdc\config::$levenshtein_ratio_person]
							)
						);
					}
				}
			} else {
				$q->where('name ',
					\npdc\config::$db['type']==='pgsql'
						? '~*'
						: 'REGEXP'
					, $string);
			}
		}
		$q->order('name');
		if(is_array($exclude) && count($exclude) > 0){
			foreach($exclude as $id){
				if(!is_numeric($id)){
					die('Hacking attempt');
				}
			}
			$q->where('person_id', 'NOT', $exclude);
		}
		return $q->get();
	}
	
	/**
	 * Check if there is no person yet with the specified mail address
	 *
	 * @param string $mail
	 * @param integer $ownId id of person being changed
	 * @return boolean true if no record is found
	 */
	public function checkMail($mail, $ownId){
		if(empty($mail)){
			return true;
		}
		return count($this->dsql->dsql()
			->table('person')
			->where('mail', $mail)
			->where('person_id', '<>', $ownId)
			->get()) === 0;
	}
	
	/**
	 * Add a person to the database
	 *
	 * @param array $data
	 * @return integer id of newly inserted person
	 */
	public function insertPerson($data){
		return \npdc\lib\Db::insert('person', $this->parseData($data), true);
	}

	/**
	 * Update person record
	 *
	 * @param array $data new data of person
	 * @param integer $person_id if of record to be updated
	 * @return void
	 */
	public function updatePerson($data, $person_id){
		return \npdc\lib\Db::update('person', $person_id, $this->parseData($data));
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
