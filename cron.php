<?php
ini_set('display_errors', 'on');
define('CALLER', 'cron');
require dirname(__FILE__).'/app/npdc/site.php';

if($_GET['key'] === \npdc\config::$cronKey){
	echo 'STARTING CRON<br/>';
	switch($_GET['action']){
		case 'daily':
			echo 'daily actions<br/>';
			//vocabs
			$controller = new \npdc\controller\Vocab();
			$controller->refreshList();
			$controller->loopVocabs();
			break;
		case 'cleanup':
			echo 'Cleanup<br/>';
			$controller = new \npdc\controller\CheckDownload($session, $args);
			$controller->cleanup();
			break;
	}
	echo 'CRON COMPLETE';
} else {
	echo 'NO ACCESS';
}