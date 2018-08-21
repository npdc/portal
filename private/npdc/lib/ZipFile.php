<?php

/**
 * Zip file generator
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */
namespace npdc\lib;

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
		
	/**
	 * Initialize zip
	 *
	 * @param string $user name of user for which zip is created
	 * @return void
	 */
	public function create($user) {
		$this->filename = date('Ymd_His').'_'.(empty($user) ? 'guest' : $user).'_'. generateRandomString(8);
		$this->dataDir = \npdc\config::$fileDir.'/';
		$this->downloadDir = \npdc\config::$downloadDir.'/';
		$this->redirect = BASE_URL.'/checkDownload/'.$this->filename;
		var_dump($this->downloadDir.$this->filename);
		if(!mkdir($this->downloadDir.$this->filename)){
			die('Could not initiate zip');
		} else {
			$this->zipModel = new \npdc\model\Zip();
			$this->zipId = $this->zipModel->insertZip(['filename'=> $this->filename]);
			$this->fileModel = new \npdc\model\File();
		}
	}
	
	
	/**
	 * Force generation of zip at end of page execution
	 */
	public function __destruct(){
		$this->generate();
	}
	
	/**
	 * Link zip to user
	 *
	 * @param integer $id user id
	 * @return void
	 */
	public function setUser($id){
		$this->zipModel->updateZip($this->zipId, ['person_id'=>$id]);
	}

	/**
	 * Link zip to dataset
	 *
	 * @param integer $dataset_id dataset id
	 * @return void
	 */
	public function setDataset($dataset_id){
		$this->zipModel->updateZip($this->zipId, ['dataset_id'=>$dataset_id]);
	}
	
	/**
	 * Set name of guest user
	 *
	 * @param string $name user details provided
	 * @return void
	 */
	public function setGuestName($name){
		$this->zipModel->updateZip($this->zipId, ['guest_user'=>$name]);
	}
	
	/**
	 * Add file to zip
	 *
	 * @param integer $id file_id
	 * @return void
	 */
	public function addFile($id){
		$file = $this->fileModel->getFile($id);
		symlink($this->dataDir.$file['location'], $this->downloadDir.$this->filename.'/'.$file['name']);
		$this->zipModel->insertFile(['zip_id'=> $this->zipId, 'file_id'=>$id]);
	}
	
	/**
	 * Add metadata to zip
	 *
	 * @param string $meta formatted metadata
	 * @return void
	 */
	public function addMeta($meta){
		file_put_contents($this->downloadDir.$this->filename.'/metadata.txt', $meta);
	}

	/**
	 * Do generate the zip
	 *
	 * @return void
	 */
	public function generate(){
		exec('cd '.$this->downloadDir.$this->filename.';zip ../'.$this->filename.'.zip * > ../'.$this->filename.'.log &');
	}
}