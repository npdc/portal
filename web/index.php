<?php

/**
 * Main index.php
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

#catch url's from the old website and redirect
if(array_key_exists('page', $_GET)){
	if(strpos($_GET['page'], '..') !== false || strpos($_GET['page'], '/') !== false){
		die('ILLEGAL URL');
	}
	//header("HTTP/1.1 301 Moved Permanently"); 
	switch (strtolower($_GET['page'])){
		case 'project_list':
			$url = 'project';
			break;
		default:
			$url = strtolower($_GET['page']);
	}
	header('Location: '.BASE_URL.'/'.$url);
	die();
}

//goto site.php, the main file
define('CALLER', 'index');
require dirname(__FILE__).'/../private/npdc/site.php';

$session = new \npdc\lib\Login();
#cut off the base url from the request_uri, remove index.php and remove any query_string
$url = substr(
	filter_input(INPUT_SERVER, 'REQUEST_URI'),
	strlen(BASE_URL)+1
);
//remove index.php
$url = (strpos($url, 'index.php') !== false) 
		? substr($url, 10) 
		: $url;
//remove query string
$url = (strpos($url, '?') !== false) 
		? substr($url, 0, strpos($url, '?')) 
		: $url;
//remove dot if present and store part after dot in ext
if(strpos($url, '.') !== false){
	list($url, $ext) = explode('.', $url);
}

define('NPDC_OUTPUT', $ext ?? getBestSupportedMimeType(['text/html'=>'html', 'application/xhtml+xml'=>'html', 'text/xml'=>'xml', 'application/xml'=>'xml']) ?? 'html');

#explode $url if substr above exists
$args = ($url === false || strlen($url)<1)
		? [] 
		: explode('/', trim($url, " \t\n\r\0\x0B/"));

if($args[0] === 'home'){
	unset($args[0]);
}
if(\Lootils\Uuid\Uuid::isValid($args[0])){
	if(strpos('-', $args[0]) === false){
		$args[0] = preg_replace("/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/i", "$1-$2-$3-$4-$5", $args[0]);
	}
	foreach(['dataset', 'project', 'publication'] as $cType){
		$mName = 'npdc\\model\\'.ucfirst($cType);
		$m = new $mName();
		$r = $m->getByUUID($args[0]);
		if($r !== false){
			unset($args[0]);
			$args = array_merge([$cType, $r[$cType.'_id'], $r[$cType.'_version']], $args);
			break;
		}
	}
}
#get controller & id
switch(count($args)){
	case 0://load homepage
		$controllerName = 'Front';
		$id = '';
		$args[0] = '';
		break;
	case 1://list a content type
		$controllerName = ucfirst($args[0]);
		$id = 'list';
		break;
	default://show a item of a type
		$controllerName = ucfirst($args[0]);
		$id = $args[1];
}

//get the view
$controllerClass = 'npdc\\controller\\'.$controllerName;
$viewClass = 'npdc\\view\\'.$controllerName;
$action = ($id==='list') ? 'showList' : 'showItem';

//if a controller/view is requested that doesn't exists or shouldn't be called directly use Page class.
if(in_array($controllerName, ['Base', 'Form']) || !file_exists(get_class_file($viewClass))){
	$viewClass = 'npdc\\view\\Page';
	$controllerName = 'Page';
	$controllerClass = 'npdc\\controller\\Page';
	$action = 'showItem';
	$id = $args[0];
}

//now load controller if it exists
$controller = (file_exists(get_class_file($controllerClass))) 
		? new $controllerClass($session, $args) 
		: null;

//load the view
$view = new $viewClass($session, $args, $controller);

//execute the view
$view->$action($id);

//format the title
//$view->title = \npdc\lib\Template::printString($view->title);

//now give the view to the page template
$template = $view->template ?? 'page';
if($session->userLevel > NPDC_PUBLIC && $view->class==="edit"){
	$extraJS = '<script type="text/javascript" src="'.BASE_URL.'/js/external/jHtmlArea/jHtmlArea-0.8.min.js?v='.APP_BUILD.'"></script><script type="text/javascript" src="'.BASE_URL.'/js/editor'.(NPDC_DEV ? '' : '.min').'.js?v='.APP_BUILD.'"></script>';
	$extraCSS = '<link rel="stylesheet" type="text/css" href="'.BASE_URL.'/css/jHtmlArea/jHtmlArea.css?v='.APP_BUILD.'" />';
}
require dirname(__FILE__).'/../private/npdc/template/'.$template.'.tpl.php';

//remove errors from the session
unset($_SESSION['errors']);