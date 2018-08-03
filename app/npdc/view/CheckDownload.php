<?php

namespace npdc\view;

class CheckDownload {
	public function __construct($session, $args, $controller){
		$this->session = $session;
		$this->args = $args;
		$this->controller = $controller;
	}

	public function showList(){
		$this->title = 'Nothing here';
		$this->mid = 'Nothing to show here';
	}
	public function showItem($id){
		if($this->controller->file === false){
			$this->title = 'File doesn\'t exist';
			$this->mid = 'Can\'t find that file';
		} else {
			switch($this->controller->status){
				case 'ready':
					$url = BASE_URL.'/'.\npdc\config::$downloadDir.'/'.$this->controller->file['filename'].'.zip';
					$this->title = 'Download ready';
					$this->mid = '<meta http-equiv="refresh" content="0; url='.$url.'">Your download is ready. If your download doesn\'t start within a second please click <a href="'.$url.'">here</a>';
					break;
				case 'error':
					$this->title = 'Error when generating your download';
					$this->mid = 'Something went wrong when generating your download. Please try again, if the error persists, please contact the NPDC';
					break;
				case 'working':
				default:
					$refresh = 1;
					$this->title = 'Generating your download';
					$this->mid = 'Generating download, please keep this page open, it will autorefresh every '.$refresh.' seconds<meta http-equiv="refresh" content="'.$refresh.'"><br/><br/>'.nl2br(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/'.\npdc\config::$downloadDir.'/'.$this->controller->file['filename'].'.log'));
			}
		}
	}
}