<?php

/**
 * Organization model
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */
namespace npdc\model;

class Organization {
	private $dsql;

	/**
	 * Constructor
	 */
	public function __construct(){
		$this->dsql = \npdc\lib\Db::getDSQLcon();
	}
	
	/**
	 * Get list of organizations
	 *
	 * @param string $filter (optional) filter, only organizations present in content type given
	 * @return array list of organizations
	 */
	public function getList($filter = null){
		$org = $this->dsql->dsql()->table('organization')
			->join('country.country_id', 'country_id', 'left')
			->field('organization_name, organization_city, organization_id')
			->order('organization_name');
		
		if(!empty($filter)){
			if(!is_array($filter)){
				$filter = ['combine'=>'any', 'type'=>[$filter]];
			}
			if(is_array($filter['type']) && count($filter['type']) > 0){
				if($filter['combine'] === 'all'){
					$qf = $org->andExpr();
				} else {
					$qf = $org->orExpr();
				}
				if(in_array('project', $filter['type'])){
					//Filter on having a project
					$inPr = $this->dsql->dsql()->table('project_person')->field('organization_id')
						->join('project', \npdc\lib\Db::joinVersion('project', 'project_person')
						->where('record_status', 'published')
					);
					$qf->where('organization_id', $inPr);
	
				}
				if(in_array('dataset', $filter['type'])){
					//Filter on having a dataset
					$inDa = $this->dsql->dsql()
						->table('dataset_person')->field('organization_id')
						->join('dataset', \npdc\lib\Db::joinVersion('dataset', 'dataset_person'))
						->where('record_status', 'published');
					$inOc = $this->dsql->dsql()
						->table('dataset')->field('originating_center', 'organization_id')
						->where('record_status', 'published');
					$inDs = $this->dsql->dsql()->table($this->dsql->expr("( [] union [] )", [$inDa, $inOc]), 'ds');
					$qf->where('organization_id', $inDs);
				}
	
				if(in_array('publication', $filter['type'])){
					//Filter on having a publication
					$inPu = $this->dsql->dsql()->table('publication_person')->field('organization_id')
						->join('publication', \npdc\lib\Db::joinVersion('publication', 'publication_person'));
					$qf->where('organization_id', $inPu);
				}
	
				//apply filter and return
				if(!empty($qf->render())){
					$org->where($qf);
				}
			}
			if(array_key_exists('country', $filter) && is_array($filter['country']) && count($filter['country']) > 0){
				$org->where('country.country_id', $filter['country']);
			}

			if(isset($filter['search'])){
				$org->where(
					$org->orExpr()
					->where('organization_name', 'LIKE', '%'.$filter['search'].'%')
					->where('historic_name', 'LIKE', '%'.$filter['search'].'%')
				);
			}
		}
		return($org->get());
	}
	
	/**
	 * Search for organizations
	 *
	 * @param string $string string to search for
	 * @param array|null $exclude (optional) list of ids of organizations to ignore in search
	 * @return array lists of organizations
	 */
	public function search($string, $exclude = null){
		$q = $this->dsql->dsql()->table('organization');
		if(!empty($string)){
			$f = $q->orExpr();
			foreach(['organization_name', 'dif_code', 'dif_name'] as $field){
				$f->where($field, (\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP'), $string);
			}
			$q->where($f);
		}
		$q->order($q->expr('(CASE WHEN country_id IN [] THEN 0 ELSE 1 END), organization_name', [\npdc\config::$defaultOrganizationFilter['country']]));
		if(is_array($exclude) && count($exclude) > 0){
			$q->where('organization_id', 'NOT', $exclude);
		}
		return $q->get();
	}
	
	/**
	 * Get organization details by id
	 *
	 * @param integer $id organization id
	 * @return array organization details
	 */
	public function getById($id){
		return $this->dsql->dsql()
			->table('organization')->field('organization.*')
			->where('organization_id', $id)
			->join('country.country_id', 'country_id', 'left')->field('country_name')
			->get()[0];
	}

	/**
	 * Get organization by uuid
	 *
	 * @param string $uuid the uuid
	 * @return array organization details
	 */
	public function getByUUID($uuid){
		return $this->dsql->dsql()
			->table('organization')->where('uuid', $uuid)
			->get()[0];
	}


	/**
	 * Get persons of organization
	 * 
	 * @param integer $organization_id
	 * @return array list of persons
	 */
	public function getPersons($organization_id){
		$l = $this->dsql->dsql()->table('person')->order('name');
		$l->where($l->orExpr()
			->where('organization_id', $organization_id)
			->where('person_id', 
				$this->dsql->dsql()
					->table('dataset_person')->field('person_id')
					->where('organization_id', $organization_id)
			)
			->where('person_id', 
				$this->dsql->dsql()
					->table('project_person')->field('person_id')
					->where('organization_id', $organization_id)
			)
			->where('person_id', 
				$this->dsql->dsql()
					->table('publication_person')->field('person_id')
					->where('organization_id', $organization_id)
			)
		);
		return $l->get();
	}

	/** 
	 * SETTERS
	 */

	/**
	 * Insert new organization
	 *
	 * @param array $data new organization details
	 * @return integer id of newly inserted record
	 */
	public function insertOrganization($data){
		return \npdc\lib\Db::insert('organization', $data, true);
	}
	
	/**
	 * Update organization details
	 *
	 * @param array $data new details of organization
	 * @param integer $id organization id
	 * @return boolean was update succesfull
	 */
	public function updateOrganization($data, $id){
		return \npdc\lib\Db::update('organization', $id, $data);
	}	
}