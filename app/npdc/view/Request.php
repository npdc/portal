<?php

namespace npdc\view;

class Request extends Base{
	protected $session;
	protected $args;
	protected $controller;
	protected $userLevelAdd = NPDC_NOBODY;
	public $class;

	public function __construct($session, $args, $controller){
		$this->session = $session;
		$this->args = $args;
		$this->controller = $controller;
		$this->model = new \npdc\model\Request();
		parent::__construct();
	}

	public function showList(){
		if($this->session->userLevel > NPDC_PUBLIC){
			$this->class = 'list request';
			$this->title = 'Data requests';
		
			$data = $this->model->getList($this->session->userId, $this->session->userLevel);
			foreach($data as &$row){
				$row['date'] = date('Y-m-d H:i', strtotime($row['request_timestamp']));
				$row['permitted'] = $this->parsePermitted($row['permitted']);
				$row['nr'] = count($this->model->getFiles($row['access_request_id']));
			}
			$this->mid = $this->displayTable('accessrequest', $data, ['date'=>'Request timestamp', 'nr'=>'Files', 'permitted'=>'Access'], ['request', 'access_request_id']);
		} else {
			$this->title = 'No access';
			$this->mid = 'Please login to view your requests';
		}
		
	}
	
	protected function parsePermitted($value){
		return $value === null 
			? 'Waiting for review' 
			: ($value == true 
				? 'Permitted' 
				: 'Denied'
		);
	}

	public function showItem($id){
		$this->class = 'detail';
		$this->data = $this->model->getById($id);
		$this->modelDataset = new \npdc\model\Dataset();
		if($this->session->userLevel === NPDC_ADMIN || $this->data['person_id'] === $this->session->userId || $this->modelDataset->isEditor($this->data['dataset-id'], $this->session->userId)){
			$this->title = 'File request '.$id;
			$this->mid = parent::parseTemplate('request_mid');
			$this->right = parent::parseTemplate('request_right');
		} else {
			$this->title = 'No access';
			$this->mid = 'You don\'t have access to this request';
		}
	}
}