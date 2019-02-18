<?php
/**
 * Lookup view
 * 
 * Allows lookup of values in forms
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */


namespace npdc\view;

class Lookup {
	private $args;
	private $session;
	private $data = [];
	
	/**
	 * Constructor
	 *
	 * @param object $session login information
	 * @param array $args url parameters
	 */
	public function __construct($session, $args){
		$this->args = $args;
		$this->session = $session;
	}
	
	/**
	 * Show option list
	 *
	 * @return void
	 */
	public function showItem(){
		if($this->session->userLevel <= NPDC_PUBLIC){
//			header('HTTP/1.0 401 Unauthorized');
//			die('No access');
		}
		$this->{$this->args['action']}();
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
	
	/**
	 * Person lookup
	 *
	 * @return void
	 */
	private function person(){
		$model = new \npdc\model\Person();
		$data = $model->search($_GET['q'], $_GET['e'] ?? null, array_key_exists('fuzzy', $_GET));
		foreach($data as $row){
			$this->data[] = [$row['person_id'], $row['name'], $row['organization_id']];
		}
	}
	
	/**
	 * Organization lookup
	 *
	 * @return void
	 */
	private function organization(){
		$model = new \npdc\model\Organization();
		if(array_key_exists('subaction', $this->args)){
			$data = [$model->getById($this->args['subaction'])];
		} else {
			$data = $model->search($_GET['q'], $_GET['e'] ?? null);
		}
		foreach ($data as $row){
			$this->data[] = [$row['organization_id'], $row['organization_name']];
		}
	}

	/**
	 * Dataset lookup
	 */
	private function dataset(){
		$model = new \npdc\model\Dataset();
		$data = $model->search($_GET['q'], false, $_GET['e'] ?? null, true);
		foreach($data as $row){
			$this->data[] = [$row['dataset_id'], $row['title']];
		}
	}
	
	/**
	 * Project lookup
	 */
	private function project(){
		$model = new \npdc\model\Project();
		$data = $model->search($_GET['q'], false, $_GET['e'] ?? null, true);
		foreach($data as $row){
			$this->data[] = [$row['project_id'], $row['title']];
		}
	}
	
	/**
	 * Publication lookup
	 *
	 * @return void
	 */
	private function publication(){
		$model = new \npdc\model\Publication();
		if(array_key_exists('doi', $_GET)){
			$this->data = $model->getByDOI(substr($_GET['doi'], strpos($_GET['doi'], '10.')));
		} elseif(array_key_exists('fuzzy', $_GET)) {
			$this->data = [];
			foreach($model->getList() as $publication){
				if(levenshtein(strtolower($publication['title']), strtolower($_GET['q']))/strlen($_GET['q']) < 0.3){
					$this->data[$publication['publication_id']] = $model->getAuthors($publication['publication_id'], $publication['publication_version'], INF).', '
					. $publication['year'].'. '
					. $publication['title'].(in_array(substr($publication['title'],-1), ['.','?']) ? '' : '.').' <i>'
					. $publication['journal'].'</i> '.$publication['volume'].' ('.$publication['issue'].'), '.$publication['pages'];
				}
			}
		} else {
			$data = $model->search($_GET['q'], false, $_GET['e'] ?? null, true);
			foreach($data as $row){
				$this->data[] = [$row['publication_id'], $row['title']];
			}
		}
	}
	
	/**
	 * Science keyword lookup
	 * 
	 * @return void
	 */
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
	
	/**
	 * Platform lookup
	 *
	 * @return void
	 */
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
	
	/**
	 * Instrument/sensor lookup
	 *
	 * @return void
	 */
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
	
	/**
	 * Location lookup
	 *
	 * @return void
	 */
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
	
	/**
	 * Session lookup
	 * 
	 * Used for check of user session when submitting. If session expired a login form is provided preventing data loss on submit
	 *
	 * @return void
	 */
	private function session(){
		$this->data=$this->session->userLevel;
	}
}