<?php

namespace npdc\view;

class Download {
	public function showItem($item){
		if(file_exists($_SERVER['DOCUMENT_ROOT']
			.'/'.\npdc\config::$fileDir
			.'/download/'.$item)){
			
			header('Content-type: application/octet-stream'); 
			header('Content-Disposition: attachment; filename='.$item); 
			header('Pragma: no-cache'); 
			header('Expires: 0');
			readfile($_SERVER['DOCUMENT_ROOT']
				.'/'.\npdc\config::$fileDir
				.'/download/'.$item);
		} else {
			http_response_code(404);
		}
		die();
	}
}