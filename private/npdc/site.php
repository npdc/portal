<?php

/**
 * The main script of the site, aranges autoload etc
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

 //record starting timestamp for recording loading time
$start = microtime(true);//for debug

define('BASE_URL', str_replace(['/cron.php', '/index.php'], '', filter_input(INPUT_SERVER, 'SCRIPT_NAME')));
define('DOCROOT_APP', __DIR__);

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__.'/');

//debugging settings
error_reporting(E_ALL & ~E_NOTICE);
define('NPDC_DB_DEBUG', false);
define('NPDC_ENVIRONMENT', $_SERVER['ENVIRONMENT'] ?? 'production');
define('NPDC_DEV', NPDC_ENVIRONMENT=='dev');
ini_set('display_errors', NPDC_DEV ? 'on' : 'off');

if(BASE_URL === '/npdc_dev'){
	ini_set('display_errors', 'on');
	define('NPDC_DEV', true);
	error_reporting(E_ALL & ~E_NOTICE);
} else {
	error_reporting(E_ALL & ~E_NOTICE);
	define('NPDC_DEV', false);
}

//get version
define('APP_VERSION', trim(file_get_contents(__DIR__.'/version')));
define('APP_BUILD', filemtime(__DIR__.'/build'));

//get first basic functions
require_once 'lib/functions.php';
require __DIR__ . '/../vendor/autoload.php';

//make classes autoloading
spl_autoload_register(
	function($className){
		$file = get_class_file($className);
		if(file_exists($file)){
			require_once $file;
		} else {
			echo 'A required file was not found '.$file;
			if(!NPDC_DEV){
				die();
			}
		}
	}
);

//Open session, clear if inactive for too long, update last activity to now
session_start();
if(($_SESSION['last_act'] ?? time()) < (time()-\npdc\config::$sessionExpire*60)){
	$_SESSION = [];
}
$_SESSION['last_act'] = time();