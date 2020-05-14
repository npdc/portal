<?php

/**
 * Vocab model
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\model;

class Vocab {
	private $dsql;

	/**
	 * Constructor
	 */
	public function __construct(){
		$this->dsql = \npdc\lib\Db::getDSQLcon();
	}
	
	/** 
	 * GETTERS
	 */

	/**
	 * Get details of a vocab based on name or id
	 *
	 * @param integer|string $id name or id of vocab
	 * @return array vocab details
	 */
	public function getVocab($id){
		$q = $this->dsql->dsql()
			->table('vocab');
		if(is_numeric($id)){
			$q->where('vocab_id', $id);
		} else {
			$q->where('vocab_name', $id);
		}
		return $q->get()[0];
	}
	
	/**
	 * Get list of vocabs that need to be updated with new details from the GCMD
	 *
	 * @return array list of vocabs
	 */
	public function getUpdatable(){
		return $this->dsql->dsql()
			->table('vocab')
			->where('sync')
			->where('last_update_local <= last_update_date')
			->where('last_update_local', '<', date('Y-m-d'))
			->get();
	}

	/**
	 * Get a vocab term by id
	 *
	 * @param string $tbl vocab to get term from
	 * @param integer $id id of term to get
	 * @return array term details
	 */
	public function getTermById($tbl, $id){
		return $this->dsql->dsql()
			->table($tbl)
			->where($tbl.'_id', $id)
			->get()[0];
	}

	/**
	 * Get a vocab term by uuid
	 *
	 * @param string $tbl vocab to get term from
	 * @param string $uuid uuid of term to get
	 * @return array term details
	 */
	public function getTermByUUID($tbl, $uuid){
		return $this->dsql->dsql()
			->table($tbl)
			->where('uuid', $uuid)
			->get()[0];
	}
	
	/**
	 * List terms in a vocab
	 *
	 * @param string $vocab vocab to list
	 * @param string|null $filter (optional) string to filter by
	 * @return array list of terms (matching filter)
	 */
	public function listTerms($vocab, $filter = null){
		$q = $this->dsql->dsql()
			->table($vocab)
			->where('visible');
		$regexp = \npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP';
		switch($vocab){
			case 'vocab_chronounit':
				$q->order('sort');
				if(!empty($filter)){
					$f = $q->orExpr();
					foreach(['eon', 'era', 'period', 'epoch', 'stage'] as $var){
						$f->where($var, $regexp, $filter);
					}
					$q->where($f);
				}
				break;
			case 'vocab_instrument':
				$q->order($q->expr('CASE WHEN category=\'NOT APPLICABLE\' THEN \'zzzzzzz\' ELSE category END, coalesce(class, \'0\'), coalesce(type, \'0\'), coalesce(subtype, \'0\'), coalesce(short_name, \'0\')'));
				if(!empty($filter)){
					$f = $q->orExpr();
					foreach(['category', 'class', 'type', 'subtype', 'short_name', 'long_name'] as $var){
						$f->where($var, $regexp, $filter);
					}
					$q->where($f);
				}
				break;
			case 'vocab_location':
				$q->order($q->expr('CASE WHEN location_category=\'NOT APPLICABLE\' THEN \'zzzzzzz\' ELSE location_category END, coalesce(location_type, \'0\'), coalesce(location_subregion1, \'0\'), coalesce(location_subregion2, \'0\'), coalesce(location_subregion3, \'0\')'));
				if(!empty($filter)){
					$f = $q->orExpr();
					foreach(['location_category', 'location_type', 'location_subregion1', 'location_subregion2', 'location_subregion3'] as $var){
						$f->where($var, $regexp, $filter);
					}
					
					$q->where($f);
				}
				break;
			case 'vocab_platform':
				$q->order($q->expr('CASE WHEN category=\'NOT APPLICABLE\' THEN \'zzzzzzz\' ELSE category END, coalesce(series_entity, \'0\'), coalesce(short_name, \'0\')'));
				if(!empty($filter)){
					$f = $q->orExpr();
					foreach(['category', 'series_entity', 'short_name', 'long_name'] as $var){
						$f->where($var, $regexp, $filter);
					}
					$q->where($f);
				}
				break;
			case 'vocab_res_hor':
			case 'vocab_res_vert':
			case 'vocab_res_time':
				$q->order('sort');
				if(!empty($filter)){
					$q->where('range', $regexp, $filter);
				}
				break;
			case 'vocab_science_keyword':
				$q->order($q->expr('category, coalesce(topic, \'0\'), coalesce(term, \'0\'), coalesce(var_lvl_1, \'0\'), coalesce(var_lvl_2, \'0\'), coalesce(var_lvl_3, \'0\'), coalesce(detailed_variable, \'0\')'));
				if(!empty($filter)){
					$f = $q->orExpr();
					foreach(['category', 'topic', 'term', 'var_lvl_1', 'var_lvl_2', 'var_lvl_3', 'detailed_variable'] as $var){
						$f->where($var, $regexp, $filter);
					}
					$q->where($f);
				}
				break;
			case 'vocab_url_type':
				$q->order($q->expr('type, coalesce(subtype, \'0\')'));
				if(!empty($filter)){
					$f = $q->orExpr();
					foreach(['type', 'subtype'] as $var){
						$f->where($var, $regexp, $filter);
					}
					$q->where($f);
				}
				break;
		}
		return $q->get();
	}

	/**
	 * Get IDN nodes based on location
	 *
	 * @param integer $location_id id of location
	 * @return array list of IDN nodes
	 */
	public function getIDNNode($location_id){
		return $this->dsql->dsql()
			->table('vocab_idn_node')
			->join('vocab_location_vocab_idn_node.vocab_idn_node_id','vocab_idn_node_id')
			->where('vocab_location_id', $location_id)
			->get();
	}
	
	/**
	 * SETTERS
	 */

	/**
	 * Add new vocab
	 *
	 * @param array $data vocab details
	 * @return boolean insert succesfull
	 */
	public function addVocab($data){
		return \npdc\lib\Db::insert('vocab', $data);
	}
	
	/**
	 * Update vocab details
	 *
	 * @param integer|string $id vocab id or name
	 * @param array $data new details
	 * @return boolean update successfull
	 */
	public function updateVocab($id, $data){
		$q = $this->dsql->dsql()->table('vocab');
		if(is_numeric($id)){
			$q->where('vocab_id', $id);
		} else {
			$q->where('vocab_name', $id);
		}
		return $q->set($data)->update();
	}
	
	/**
	 * Add new term to a vocab
	 *
	 * @param string $tbl vocab to add to
	 * @param array $values data of term to add
	 * @return boolean insert succesfull
	 */
	public function insertTerm($tbl, $values){
		return \npdc\lib\Db::insert($tbl, $values);
	}
	
	/**
	 * Update term
	 *
	 * @param string $tbl name of vocab
	 * @param integer $id id of term
	 * @param array $values new data of term
	 * @return boolean update succesfull
	 */
	public function updateTerm($tbl, $id, $values){
		\npdc\lib\Db::update($tbl,$id,$values);
	}
}