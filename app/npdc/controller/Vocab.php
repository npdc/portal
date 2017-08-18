<?php

namespace npdc\controller;

class Vocab {
	private $model;
	private $curl;
	
	public function __construct(){
		$this->model = new \npdc\model\Vocab();
		$this->curl = new \npdc\lib\CurlWrapper();
		$this->curl->httpauth(\npdc\config::$gcmd['user'], \npdc\config::$gcmd['pass']);
	}
	private function setValue($value){
		return empty($value) ? null : $value;
	}
	
	public function refreshList(){
		echo 'refresh list <br/>';
		if($this->model->getVocab(1)['last_update_local']<date('Y-m-d')){
			$url = 'https://gcmdservices.gsfc.nasa.gov/kms/concept_schemes';
			$res = simplexml_load_string($this->curl->get($url));
			if($this->curl->status()['http_code'] === 200){
				foreach($res->scheme as $scheme){
					echo $scheme['id'].': '.$scheme['updateDate'].'<br/>';
					if($this->model->getVocab($scheme['name']) === false){
						$this->model->addVocab(['vocab_id'=>(int)$scheme['id'], 'vocab_name'=>(string)$scheme['name'], 'last_update_date'=>(string)$scheme['updateDate']]);
					} else {
						$this->model->updateVocab($scheme['name'], ['vocab_name'=>(string)$scheme['name'], 'last_update_date'=>(string)$scheme['updateDate']]);
					}
				}
				$this->model->updateVocab(1, ['last_update_local'=>date('Y-m-d')]);
			}	
		}
	}
	
	public function loopVocabs(){
		$vocabs = $this->model->getUpdatable();
		foreach($vocabs as $vocab){
			echo 'Starting with '.$vocab['vocab_name'].' - '.$vocab['vocab_id']."\r\n";
			$url = 'https://gcmdservices.gsfc.nasa.gov/static/kms/'.$vocab['vocab_name'].'/'.$vocab['vocab_name'].'.csv';
			$url = 'https://gcmdservices.gsfc.nasa.gov/kms/concepts/concept_scheme/'.$vocab['vocab_name'].'?format=csv';
			$csv = str_getcsv($this->curl->get($url), "\n");
			$comment_lines = 1;
			$keys = str_getcsv($csv[$comment_lines]);
			$data = array_slice($csv, $comment_lines+1);
			foreach($data as $row){
				if(count($keys) !== count(str_getcsv($row))){
					break;
				}
				$row = array_combine($keys, str_getcsv($row));
				$uuid = $row['UUID'];
				if(!empty($uuid)){
					switch($vocab['vocab_name']){
						case 'chronounits':
							$tbl = 'vocab_chronounit';
							$values = ['eon'=>$this->setValue($row['Eon'])
								, 'era'=>$this->setValue($row['Era'])
								, 'period'=>$this->setValue($row['Period'])
								, 'epoch'=>$this->setValue($row['Epoch'])
								, 'stage'=>$this->setValue($row['Stage'])
								];
							break;
						case 'locations':
							$tbl = 'vocab_location';
							$values = ['location_category'=>$this->setValue($row['Location_Category'])
								, 'location_type'=>$this->setValue($row['Location_Type'])
								, 'location_subregion1'=>$this->setValue($row['Location_Subregion1'])
								, 'location_subregion2'=>$this->setValue($row['Location_Subregion2'])
								, 'location_subregion3'=>$this->setValue($row['Location_Subregion3'])
								];
							break;
						case 'platforms':
							$tbl = 'vocab_platform';
							$values = ['category'=>$this->setValue($row['Category'])
								, 'series_entity'=>$this->setValue($row['Series_Entity'])
								, 'short_name'=>$this->setValue($row['Short_Name'])
								, 'long_name'=>$this->setValue($row['Long_Name'])
								];
							break;
						case 'instruments':
							$tbl = 'vocab_instrument';
							$values = ['category'=>$this->setValue($row['Category'])
								, 'class'=>$this->setValue($row['Class'])
								, 'type'=>$this->setValue($row['Type'])
								, 'subtype'=>$this->setValue($row['Subtype'])
								, 'short_name'=>$this->setValue($row['Short_Name'])
								, 'long_name'=>$this->setValue($row['Long_Name'])
								];
							break;
						case 'isotopiccategory':
							$tbl = 'vocab_iso_topic_category';
							//TODO andere databron inbouwen!!!!
							continue 2;
							break;
						case 'idnnode':
							$tbl = 'vocab_idn_node';
							$values = ['short_name'=>$this->setValue($row['Short_Name']),
								'long_name'=>$this->setValue($row['Long_Name'])
							];
							break;
						case 'rucontenttype':
							$tbl = 'vocab_url_type';
							$values = ['type'=>$this->setValue($row['Type'])
								,'subtype'=>$this->setValue($row['Subtype'])
								];
							break;
						case 'horizontalresolutionrange':
							$tbl = 'vocab_res_hor';
							$values = ['`range`'=>$this->setValue($row['Horizontal_Resolution_Range'])];
							break;
						case 'verticalresolutionrange':
							$tbl = 'vocab_res_vert';
							$values = ['`range`'=>$this->setValue($row['Vertical_Resolution_Range'])];
							break;
						case 'temporalresolutionrange':
							$tbl = 'vocab_res_time';
							$values = ['`range`'=>$this->setValue($row['Temporal_Resolution_Range'])];
							break;
						case 'sciencekeywords':
							$tbl = 'vocab_science_keyword';
							$values = ['category'=>$this->setValue($row['Category'])
								, 'topic'=>$this->setValue($row['Topic'])
								, 'term'=>$this->setValue($row['Term'])
								, 'var_lvl_1'=>$this->setValue($row['Variable_Level_1'])
								, 'var_lvl_2'=>$this->setValue($row['Variable_Level_2'])
								, 'var_lvl_3'=>$this->setValue($row['Variable_Level_3'])
								, 'detailed_variable'=>$this->setValue($row['Detailed_Variable'])
								];
							break;
						default:
							echo $vocab['vocab_name'].' not implemented';
							continue 2;
					}
					$rec = $this->model->getTermByUUID($tbl, $uuid);
					if($rec === false){
						$values['uuid'] = $uuid;
						$this->model->insertTerm($tbl, $values);
					} else {
						$this->model->updateTerm($tbl, $rec[$tbl.'_id'], $values);
					}
				}
			}
			$this->model->updateVocab($vocab['vocab_id'], ['last_update_local'=>date('Y-m-d')]);
		}
	}
}