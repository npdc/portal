<?php

/**
 * Check if zip file with data is available, and if not, why not
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\controller;

class CheckDownload {
	public $status;
	public $file;
	private $model;

	/**
	 * Constructor
	 *
	 * @param [type] $session
	 * @param [type] $args
	 */
	public function __construct($session, $args){
		if(CALLER === 'index'){
			$this->model = new \npdc\model\Zip();
			$this->file = $this->model->getByName($args[1]);
			if($this->file !== false){
				if($file->timestamp < "1 week geleden" && false){
					$this->status = 'expired';
				} elseif(file_exists(\npdc\config::$downloadDir.'/'.$this->file['filename'].'.zip')){
					$this->status = 'ready';
				} elseif (file_exists(\npdc\config::$downloadDir.'/'.$this->file['filename']) && file_exists(\npdc\config::$downloadDir.'/'.$this->file['filename'].'.log')){
					$this->status = 'working';	
				} else {
					$this->status = 'error';
				}
			}
		}
		
	}
	
	public function __destruct(){
		if(CALLER === 'index'){
			if($this->status === 'ready'){
				if(file_exists(\npdc\config::$downloadDir.'/'.$this->file['filename'])){
					$this->delTree(\npdc\config::$downloadDir.'/'.$this->file['filename']);
				}
			}
		}
	}
	
	public function cleanup(){
		$dir = \npdc\config::$downloadDir;
		echo 'Checking '.$dir.'<br/>';
		$files = array_diff(scandir($dir), array('.', '..'));
		foreach($files as $file){
			if(filemtime($dir.'/'.$file) < time()-7*24*60*60){
				echo 'Removing '.$file.'<br/>';
				is_dir($dir.'/'.$file)
					? $this->delTree($dir.'/'.$file) 
					: unlink($dir.'/'.$file); 
			}
		}
	}

	private function delTree($dir) { 
		$files = array_diff(scandir($dir), array('.','..')); 
		foreach ($files as $file) { 
			is_dir($dir.'/'.$file)
				? $this->delTree($dir.'/'.$file) 
				: unlink($dir.'/'.$file); 
		}
		return rmdir($dir); 
	}
}