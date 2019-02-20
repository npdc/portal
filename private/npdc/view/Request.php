<?php

/**
 * Data access request view
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */
namespace npdc\view;

class Request extends Base{
	protected $session;
	protected $controller;
	protected $userLevelAdd = NPDC_NOBODY;
	public $class;

	/**
	 * Constructor
	 *
	 * @param object $session login information
	 *
	 * @param object $controller request controller
	 */
	public function __construct($session, $controller){
		$this->session = $session;
		$this->controller = $controller;
		$this->model = new \npdc\model\Request();
		parent::__construct();
		$this->extraHeader = '<meta name="robots" content="noindex">';
	}

	/**
	 * Display list of requests
	 *
	 * @return void
	 */
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
	
	/**
	 * Parse the permitted status to human readable
	 *
	 * @param boolean|null $value
	 * @return string permitted status
	 */
	protected function parsePermitted($value){
		return $value === null 
			? 'Waiting for review' 
			: ($value == true 
				? 'Permitted' 
				: 'Denied'
		);
	}

	/**
	 * Show single request
	 *
	 * @param integer $id Request id
	 * @return void
	 */
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