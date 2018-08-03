<?php


namespace npdc\model;

class ZipFile {
	private $zip;
	public $filename;
	public $zipId;
	private $fileModel;
	private $zipModel;
	private $dataDir;
	private $downloadDir;
	public $redirect;
	
	public function __construct(){}
		
	public function create($user) {
		$this->filename = date('Ymd_His').'_'.(empty($user) ? 'guest' : $user).'_'. generateRandomString(8);
		$this->dataDir = $_SERVER['DOCUMENT_ROOT'].'/'.\npdc\config::$fileDir.'/';
		$this->downloadDir = $_SERVER['DOCUMENT_ROOT'].'/'.\npdc\config::$downloadDir.'/';
		$this->redirect = BASE_URL.'/checkDownload/'.$this->filename;
		if(!mkdir($this->downloadDir.$this->filename)){
			die('Could not initiate zip');
		} else {
			$this->zipModel = new \npdc\model\Zip();
			$this->zipId = $this->zipModel->insertZip(['filename'=> $this->filename]);
			$this->fileModel = new \npdc\model\File();
		}
	}
	
	public function __destruct(){
		$this->generate();
	}
	
	public function setUser($id){
		$this->zipModel->updateZip($this->zipId, ['person_id'=>$id]);
	}
	public function setDataset($dataset_id){
		$this->zipModel->updateZip($this->zipId, ['dataset_id'=>$dataset_id]);
	}
	
	public function setGuestName($name){
		$this->zipModel->updateZip($this->zipId, ['guest_user'=>$name]);
	}
	
	public function addFile($id){
		$file = $this->fileModel->getFile($id);
		symlink($this->dataDir.$file['location'], $this->downloadDir.$this->filename.'/'.$file['name']);
		$this->zipModel->insertFile(['zip_id'=> $this->zipId, 'file_id'=>$id]);
	}
	
	public function addMeta($meta){
		file_put_contents($this->downloadDir.$this->filename.'/metadata.txt', $meta);
	}

	public function generate(){
		exec('cd '.$this->downloadDir.$this->filename.';zip ../'.$this->filename.'.zip * > ../'.$this->filename.'.log &');
	}
}