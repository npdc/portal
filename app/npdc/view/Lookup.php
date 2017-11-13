<?php

namespace npdc\view;

class Lookup {
	private $args;
	private $session;
	private $data = [];
	
	public function __construct($session, $args){
		$this->args = $args;
		$this->session = $session;
	}
	
	public function showItem(){
		if($this->session->userLevel <= NPDC_PUBLIC){
//			header('HTTP/1.0 401 Unauthorized');
//			die('No access');
		}
		$this->{$this->args[1]}();
		header('Content-type:application/json;charset=utf-8');
		if($_GET['output'] === 'object'){
			foreach($this->data as &$row){
				$row = ['id'=>$row[0], 'text'=>htmlspecialchars_decode($row[1])];
			}
			$this->data = (object) ['items'=>$this->data];
		}
		echo json_encode($this->data);
		die();
	}
	
	private function person(){
		$model = new \npdc\model\Person();
		$data = $model->search($_GET['q'], $_GET['e'] ?? null, array_key_exists('fuzzy', $_GET));
		foreach($data as $row){
			$this->data[] = [$row['person_id'], $row['name'], $row['organization_id']];
		}
	}
	
	private function organization(){
		$model = new \npdc\model\Organization();
		if(count($this->args) > 2){
			$data = [$model->getById($this->args[2])];
		} else {
			$data = $model->search($_GET['q'], $_GET['e'] ?? null);
		}
		foreach ($data as $row){
			$this->data[] = [$row['organization_id'], $row['organization_name']];
		}
	}

	private function dataset(){
		$model = new \npdc\model\Dataset();
		$data = $model->search($_GET['q'], false, $_GET['e'] ?? null, true);
		foreach($data as $row){
			$this->data[] = [$row['dataset_id'], $row['title']];
		}
	}
	
	private function project(){
		$model = new \npdc\model\Project();
		$data = $model->search($_GET['q'], false, $_GET['e'] ?? null, true);
		foreach($data as $row){
			$this->data[] = [$row['project_id'], $row['title']];
		}
	}
	
	private function publication(){
		$model = new \npdc\model\Publication();
		$data = $model->search($_GET['q'], false, $_GET['e'] ?? null, true);
		foreach($data as $row){
			$this->data[] = [$row['publication_id'], $row['title']];
		}
	}
	
	private function science_keyword(){
		$vocab = new \npdc\lib\Vocab();
		$data = $vocab->getList('vocab_science_keyword', $_GET['q']);
		$output = [];
		$output2 = [];
		foreach($data as $id=>$val){
			if(true || !in_array($id, $_GET['e'] ?? [])){
				$output[] = ['id'=>$id, 'label'=>$val];
				$output2[] = [$id, $val, substr_count($val, '>')];
			}
		}
		$this->data = ($_GET['output'] === 'object') 
			? ['total_count'=>count($output), 'items'=>$output]
			: $output2;
	}
	
	private function platform(){
		$vocab = new \npdc\lib\Vocab();
		$data = $vocab->getList('vocab_platform', $_GET['q']);
		$output = [];
		$output2 = [];
		foreach($data as $id=>$val){
			if(true || !in_array($id, $_GET['e'] ?? [])){
				$output[] = ['id'=>$id, 'label'=>$val];
				$output2[] = [$id, $val, substr_count($val, '>')];
			}
		}
		$this->data = ($_GET['output'] === 'object') 
			? ['total_count'=>count($output), 'items'=>$output]
			: $output2;
		
	}
	
	private function instrument(){
		$vocab = new \npdc\lib\Vocab();
		$data = $vocab->getList('vocab_instrument', $_GET['q']);
		$output = [];
		$output2 = [];
		foreach($data as $id=>$val){
			if(true || !in_array($id, $_GET['e'] ?? [])){
				$output[] = ['id'=>$id, 'label'=>$val];
				$output2[] = [$id, $val, substr_count($val, '>')];
			}
		}
		$this->data = ($_GET['output'] === 'object') 
			? ['total_count'=>count($output), 'items'=>$output]
			: $output2;
		
	}
	
	private function location(){
		$vocab = new \npdc\lib\Vocab();
		$data = $vocab->getList('vocab_location', $_GET['q']);
		$output = [];
		$output2 = [];
		foreach($data as $id=>$val){
			if(true || !in_array($id, $_GET['e'] ?? [])){
				$output[] = ['id'=>$id, 'label'=>$val];
				$output2[] = [$id, $val, substr_count($val, '>')];
			}
		}
		$this->data = ($_GET['output'] === 'object') 
			? ['total_count'=>count($output), 'items'=>$output]
			: $output2;
		
	}
	
	private function session(){
		$this->data=$this->session->userLevel;
	}
}