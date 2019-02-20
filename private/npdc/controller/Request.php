<?php

/**
 * Data request controller
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\controller;

class Request extends Base {
	public $userLevelAdd = NPDC_NOBODY;//minimum user level required to add a new dataset
	
	/**
	 * Constructor
	 *
	 * @param object $session login information
	 *
	 */
	public function __construct($session) {
		$this->model = new \npdc\model\Request();
		$this->session = $session;
		$request = $this->model->getById(\npdc\lib\Args::get('id'));
		$this->modelDataset = new \npdc\model\Dataset();
		if(($this->session->userLevel === NPDC_ADMIN || $this->modelDataset->isEditor($request['dataset_id'], $this->session->userId)) && array_key_exists('reason', $_POST)){
			if(empty($_POST['allow'])){
				$this->error = 'Please select if the user can get access to the requested file(s)';
			} elseif ($_POST['allow'] === 'no' && empty($_POST['reason'])){
				$this->error = 'Please provide a reason the user cant get access to the requested file(s)';
			} else {
				$this->saveAccess();
			}
		}
	}
	
	/**
	 * Save response of researcher on request and notify requester
	 *
	 * @return void
	 */
	private function saveAccess(){
		$this->model->updateRequest(\npdc\lib\Args::get('id'), ['permitted'=>($_POST['allow']==='yes' ? 1 : 0),'response'=>$_POST['reason'],'response_timestamp'=>date('Y-m-d h:i:s'), 'responder_id'=>$this->session->userId]);
		$request = $this->model->getById(\npdc\lib\Args::get('id'));
		$personModel = new \npdc\model\Person();
		$person = $personModel->getById($request['person_id']);
			
		if($_POST['allow']==='yes'){
			$zip = new \npdc\lib\ZipFile();
			$zip->create(preg_replace('/[^A-Za-z0-9\-_]/', '', str_replace(' ', '_', $person['name'])));
			$zip->setDataset($request['dataset_id']);
			if(!empty($request['person_id'])){
				$zip->setUser($request['person_id']);
			}
			$zip->addMeta($this->modelDataset->generateMeta($request['dataset_id']));
			foreach($this->model->getFiles(\npdc\lib\Args::get('id')) as $file){
				$zip->addFile($file['file_id']);
			}
			$this->model->updateRequest($request['access_request_id'], ['zip_id'=>$zip->zipId]);
		}
		$mailText = 'Hi '.$person['name']."\r\n\r\nYour request to access the files below has been ".($_POST['allow'] === 'yes' ? 'accepted' : 'rejected').'. ';
		if(!empty($_POST['reason'])){
			$mailText .= "The reviewer gave the following comments:\r\n".$_POST['reason'];
		}
		$mailText .= "\r\n\r\nYou requested the following files:\r\n";
		foreach($this->model->getFiles(\npdc\lib\Args::get('id')) as $file){
			$mailText .= '- '.$file['name']."\r\n";
		}
		$mailText .= "\r\n";
		if($_POST['allow'] === 'yes'){
			$mailText .= 'You can download the files at '.getProtocol().$_SERVER['HTTP_HOST'].$zip->redirect.".zip\r\n\r\n";
		}
		$mailText .= "\r\n\r\nKind regards,\r\n". \npdc\config::$siteName;
		$mail = new \npdc\lib\Mailer();
		$mail->to($person['mail'], $person['name']);
		$mail->subject('Data request '.($_POST['allow'] === 'yes' ? 'accepted' : 'rejected'));
		$mail->text($mailText);
		$mail->send();
		
		$this->notice = 'Your reply has been saved';
	}
}