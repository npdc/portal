<?php

/**
 * Download view
 * 
 * Allows the download dir to be outside the webroot
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\view;

class Download {
	/**
	 * Retreive the file and offer as download
	 *
	 * @param string $item name of the file to be downloaded
	 * @return void
	 */
	public function showItem($item){
		$file = \npdc\config::$downloadDir.'/'.$item.'.'.NPDC_OUTPUT;
		if(file_exists($file)){
			header('Content-type: application/octet-stream'); 
			header('Content-Disposition: attachment; filename='.$item.'.'.NPDC_OUTPUT);
			header('Pragma: no-cache'); 
			header('Expires: 0');
			readfile($file);
		} else {
			http_response_code(404);
			echo 'File not found';
		}
		die();
	}
}