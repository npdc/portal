<?php

/**
 * error logging
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\lib;

class Error {
	/**
	 * log an error
	 * @param string $error the error
	 * @param boolean $fatal stop script execution after logging the error (default: false)
	 */
	public function log($error, $fatal = false){
		
		$function = debug_backtrace()[1]['class'].'::'.debug_backtrace()[1]['function'].' ('.debug_backtrace()[0]['file'].':'.  debug_backtrace()[0]['line'].')';
		$caller = debug_backtrace()[2]['class'].'::'.debug_backtrace()[2]['function'].' ('.debug_backtrace()[1]['file'].':'.  debug_backtrace()[2]['line'].')';
		if(NPDC_DEV){
			$_SESSION['errors'] .= $error.'<br/>- '.$function.'<br/>&nbsp;&nbsp;- '.$caller.'<br/>';
		}
		if($fatal){
			http_response_code(500);
			echo 'The script encountered an error. We are very sorry.<br/><br/>';
			echo $_SESSION['errors'];
			unset($_SESSION['errors']);
			die();
		}
	}
}

