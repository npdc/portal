<?php


namespace npdc\model;

class ZipFile {
	private $zip;
	public $filename;
	public $zipId;
	private $fileModel;
	private $zipModel;
	private $dir;
	
	public function __construct($user) {
		$this->zip = new \ZipArchive();
		$this->filename = date('Ymd_His').'_'.(empty($user) ? 'guest' : $user).'_'. generateRandomString(8).'.zip';
		$this->dir = $_SERVER['DOCUMENT_ROOT']
			.'/'.\npdc\config::$fileDir
			.'/';
		if($this->zip->open($_SERVER['DOCUMENT_ROOT'].'/'.\npdc\config::$downloadDir.'/'.$this->filename, \ZipArchive::CREATE) !== true){
			die('Could not create zip file');
		} else {
			$this->zipModel = new \npdc\model\Zip();
			$this->zipId = $this->zipModel->insertZip(['filename'=> $this->filename]);
			$this->fileModel = new \npdc\model\File();
		}
	}
	
	public function __destruct(){
		$this->zip->close();
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
		if($this->zip->addFile($this->dir.$file['location'], $file['name'])){
			$this->zipModel->insertFile(['zip_id'=> $this->zipId, 'file_id'=>$id]);
		}
	}
	
	public function addMeta($meta){
		$this->zip->addFromString('metadata.txt', $meta);
	}
}