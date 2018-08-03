<?php

namespace npdc\controller;

class Request extends Base {
	public $userLevelAdd = NPDC_NOBODY;//minimum user level required to add a new dataset
	
	public function __construct($session, $args) {
		$this->model = new \npdc\model\Request();
		$this->session = $session;
		$this->args = $args;
		$request = $this->model->getById($this->args[1]);
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
	
	private function saveAccess(){
		$this->model->updateRequest($this->args[1], ['permitted'=>($_POST['allow']==='yes' ? 1 : 0),'response'=>$_POST['reason'],'response_timestamp'=>'now()', 'responder_id'=>$this->session->userId]);
		$request = $this->model->getById($this->args[1]);
		$personModel = new \npdc\model\Person();
		$person = $personModel->getById($request['person_id']);
			
		if($_POST['allow']==='yes'){
			$zip = new \npdc\model\ZipFile();
			$zip->create(preg_replace('/[^A-Za-z0-9\-_]/', '', str_replace(' ', '_', $person['name'])));
			$zip->setDataset($request['dataset_id']);
			if(!empty($request['person_id'])){
				$zip->setUser($request['person_id']);
			}
			$zip->addMeta($this->modelDataset->generateMeta($request['dataset_id']));
			foreach($this->model->getFiles($this->args[1]) as $file){
				$zip->addFile($file['file_id']);
			}
			$this->model->updateRequest($request['access_request_id'], ['zip_id'=>$zip->zipId]);
		}
		$mailText = 'Hi '.$person['name']."\r\n\r\nYour request to access the files below has been ".($_POST['allow'] === 'yes' ? 'accepted' : 'rejected').'. ';
		if(!empty($_POST['reason'])){
			$mailText .= "The reviewer gave the following comments:\r\n".$_POST['reason'];
		}
		$mailText .= "\r\n\r\nYou requested the following files:\r\n";
		foreach($this->model->getFiles($this->args[1]) as $file){
			$mailText .= '- '.$file['name']."\r\n";
		}
		$mailText .= "\r\n";
		if($_POST['allow'] === 'yes'){
			$mailText .= 'You can download the files at '.getProtocol().$_SERVER['HTTP_HOST'].$zip->redirect."\r\n\r\n";
		}
		$mailText .= "\r\n\r\nKind regards,\r\n". \npdc\config::$siteName;
		$mail = new \npdc\lib\Mailer();
		$mail->to($person['mail'], $person['name']);
		$mail->subject('Data request '.($_POST['allow'] === 'yes' ? 'accepted' : 'rejected'));
		$mail->text = $mailText;
		$mail->send();
		
		$this->notice = 'Your reply has been saved';
	}
}