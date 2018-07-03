<?php

namespace npdc\model;

class Vocab {
	protected $fpdo;
	
	/**
	 * set db instance
	 */
	public function __construct(){
		$this->fpdo = \npdc\lib\Db::getFPDO();
	}
	
	public function getVocab($id){
		if(is_numeric($id)){
			return $this->fpdo->from('vocab', $id)->fetch();
		} else {
			return $this->fpdo->from('vocab')->where('vocab_name', $id)->fetch();
		}
	}
	
	public function getUpdatable(){
		return $this->fpdo
			->from('vocab')
			->where('sync')
			->where('last_update_local <= last_update_date')
			->where('last_update_local < ?', date('Y-m-d'))
			->fetchAll();
	}
	
	public function addVocab($data){
		return $this->fpdo->insertInto('vocab', $data)->execute();
	}
	
	public function updateVocab($id, $data){
		if(is_numeric($id)){
			return $this->fpdo->update('vocab', $data, $id)->execute();
		} else {
			return $this->fpdo
				->update('vocab')
				->where('vocab_name', $id)
				->set($data)
				->execute();
		}
	}
	
	public function getTermById($tbl, $id){
		return $this->fpdo->from($tbl, $id)->fetch();
	}

	public function getTermByUUID($tbl, $uuid){
		return $this->fpdo->from($tbl)->where('uuid', $uuid)->fetch();
	}
	
	public function insertTerm($tbl, $values){
		return $this->fpdo->insertInto($tbl, $values)->execute();
	}
	
	public function updateTerm($tbl, $id, $values){
		return $this->fpdo->update($tbl, $values, $id)->execute();
	}
	
	public function listTerms($vocab, $filter = null){
		$q = $this->fpdo->from($vocab)->where('visible');
		switch($vocab){
			case 'vocab_chronounit':
				$q->orderBy('sort');
				if(!empty($filter)){
					$q->where('(eon '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter1 OR era '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter2 OR period '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter3 OR epoch '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter4 OR stage '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter5)', [':filter1'=>$filter, ':filter2'=>$filter, ':filter3'=>$filter, ':filter4'=>$filter, ':filter5'=>$filter]);
				}
				break;
			case 'vocab_instrument':
				$q->orderBy('CASE WHEN category=\'NOT APPLICABLE\' THEN \'zzzzzzz\' ELSE category END, coalesce(class, \'0\'), coalesce(type, \'0\'), coalesce(subtype, \'0\'), coalesce(short_name, \'0\')');
				if(!empty($filter)){
					$q->where('(category '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter1 OR class '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter2 OR type '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter3 OR subtype '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter4 OR short_name '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter5 OR long_name '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter6)', [':filter1'=>$filter, ':filter2'=>$filter, ':filter3'=>$filter, ':filter4'=>$filter, ':filter5'=>$filter, ':filter6'=>$filter]);
				}
				break;
			case 'vocab_location':
				$q->orderBy('CASE WHEN location_category=\'NOT APPLICABLE\' THEN \'zzzzzzz\' ELSE location_category END, coalesce(location_type, \'0\'), coalesce(location_subregion1, \'0\'), coalesce(location_subregion2, \'0\'), coalesce(location_subregion3, \'0\')');
				if(!empty($filter)){
					$q->where('(location_category '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter1 OR location_type '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter2 OR location_subregion1 '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter3 OR location_subregion2 '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter4 OR location_subregion3 '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter5)', [':filter1'=>$filter, ':filter2'=>$filter, ':filter3'=>$filter, ':filter4'=>$filter, ':filter5'=>$filter]);
				}
				break;
			case 'vocab_platform':
				$q->orderBy('CASE WHEN category=\'NOT APPLICABLE\' THEN \'zzzzzzz\' ELSE category END, coalesce(series_entity, \'0\'), coalesce(short_name, \'0\')');
				if(!empty($filter)){
					$q->where('(category '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter1 OR series_entity '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter2 OR short_name '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter3 OR long_name '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter4)', [':filter1'=>$filter, ':filter2'=>$filter, ':filter3'=>$filter, ':filter4'=>$filter]);
				}
				break;
			case 'vocab_res_hor':
			case 'vocab_res_vert':
			case 'vocab_res_time':
				$q->orderBy('sort');
				if(!empty($filter)){
					$q->where('range '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter', [':filter'=>$filter]);
				}
				break;
			case 'vocab_science_keyword':
				$q->orderBy('category, coalesce(topic, \'0\'), coalesce(term, \'0\'), coalesce(var_lvl_1, \'0\'), coalesce(var_lvl_2, \'0\'), coalesce(var_lvl_3, \'0\'), coalesce(detailed_variable, \'0\')');
				if(!empty($filter)){
					$q->where('(category '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter1 OR topic '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter2 OR term '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter3 OR var_lvl_1 '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter4 OR var_lvl_2 '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter5 OR var_lvl_3 '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter6 OR detailed_variable '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter7)', [':filter1'=>$filter, ':filter2'=>$filter, ':filter3'=>$filter, ':filter4'=>$filter, ':filter5'=>$filter, ':filter6'=>$filter, ':filter7'=>$filter]);
				}
				break;
			case 'vocab_url_type':
				$q->orderBy('type, coalesce(subtype, \'0\')');	
				if(!empty($filter)){
					$q->where('(type '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter1 OR subtype '.(\npdc\config::$db['type']==='pgsql' ? '~*' : 'REGEXP').' :filter2)', [':filter1'=>$filter, ':filter2'=>$filter]);
				}
				break;
		}
		return $q->fetchAll();
	}

	public function getIDNNode($location_id){
		return $this->fpdo
			->from('vocab_idn_node')
			->join('vocab_location_vocab_idn_node USING(vocab_idn_node_id)')
			->where('vocab_location_id', $location_id)
			->fetchAll();
	}
}