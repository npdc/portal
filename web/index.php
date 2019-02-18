<?php

/**
 * Main index.php
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */
if(file_exists('oldSite.php')){include 'oldSite.php';}//redirects from old urls

//goto site.php, the main file
define('CALLER', 'index');
require dirname(__FILE__).'/../private/npdc/site.php';

$session = new \npdc\lib\Login();

#cut off the base url from the request_uri and feed to regexp
$url = substr(
	filter_input(INPUT_SERVER, 'REQUEST_URI'),
	strlen(BASE_URL)+1
);
$pattern = '^' //start at beginning of string
	. '(index.php[\/\?])?' //trim index.php
	. '('
		. '('//search
			. '(?P<search>search)\/((?P<types>[a-z+]{1,})\/)?(?P<subject>(.*))'
		. ')|('//login or register
			. '(?P<logintype>login|register)'
			. '(\/'
				. '(?P<loginaction>[a-z]{1,})'
			.')?'
			. '(\/'
				. '(?P<loginid>[0-9]{1,})'
				. '(\/'
					. '(?P<loginkey>[a-z0-9]{1,})'
				. ')?'
			. ')?'
		. ')|('//all others
			. '('
				. '('//uuid (with or without type)
					. '((?P<uuidtype>[a-z]{1,})\/)?(?P<uuid>[a-f0-9]{8}(-)?(?:[a-f0-9]{4}(-)?){3}[a-f0-9]{12})'
				. ')|('//type with optional id and optional version
					. '(?P<type>[a-z]{1,})'
					. '(\/'
						. '(?P<id>[0-9]{1,})'
						. '(\/'
							. '(?P<version>[0-9]{1,})'
						. ')?'
					. ')?'
				. ')'
			.')'
			. '(\/'//action for page
				. '(?P<action>[a-z]{1,})'
				. '(\/'
					. '(?P<subaction>[a-z\ +]{1,})'
				.')?'
			. ')?'//get extention
			. '(\.(?P<ext>[a-z0-9]{1,}))?'
		. ')'
	.')?';

preg_match('/'.$pattern.'/i',str_replace('%20', ' ', $url), $args);
foreach($args as $key=>$val){
	if(is_numeric($key) || $val === ''){
		unset($args[$key]);
	}
}
if(array_key_exists('search',$args)){
	$args['type'] = 'search';
	unset($args['search']);
}

if(array_key_exists('logintype', $args)){
	$args['type'] = $args['logintype'];
	if(array_key_exists('loginid', $args)){$args['id'] = $args['loginid'];}
}

define('NPDC_OUTPUT', $args['ext'] ?? getBestSupportedMimeType(['text/html'=>'html', 'application/xhtml+xml'=>'html', 'text/xml'=>'xml', 'application/xml'=>'xml']) ?? 'html');

if(array_key_exists('uuid', $args)){
	if(\Lootils\Uuid\Uuid::isValid($args['uuid'])){
		if(strpos('-', $args['uuid']) === false){
			$args['uuid'] = preg_replace("/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/i", "$1-$2-$3-$4-$5", $args['uuid']);
		}
		foreach(array_key_exists('uuidtype', $args) ? [$args['uuidtype']] : ['dataset', 'project', 'publication'] as $cType){
			$mName = 'npdc\\model\\'.ucfirst($cType);
			$m = new $mName();
			$r = $m->getByUUID($args['uuid']);
			if($r !== false){
				$args = array_merge(['type'=>$cType, 'id'=>$r[$cType.'_id'], 'version'=>$r[$cType.'_version']], $args);
				break;
			}
		}
	}
	if(!array_key_exists('type', $args)){
		$args['type'] = $args['uuidtype'] ?? '404';
		$args['id'] = 0;
	}
}
if(empty($args['type'])){
	$args['type'] = 'front';
}
$controllerName = ucfirst($args['type']);
$action = array_key_exists('id', $args) || array_key_exists('action', $args) ? 'showItem' : 'showList';

//get the view
$controllerClass = 'npdc\\controller\\'.$controllerName;
$viewClass = 'npdc\\view\\'.$controllerName;

//if a controller/view is requested that doesn't exists or shouldn't be called directly use Page class.
if(in_array($controllerName, ['Base', 'Form']) || !file_exists(get_class_file($viewClass))){
	$viewClass = 'npdc\\view\\Page';
	$controllerName = 'Page';
	$controllerClass = 'npdc\\controller\\Page';
	$action = 'showItem';
	$args['id'] = $args['type'];
}

//now load controller if it exists
$controller = (file_exists(get_class_file($controllerClass))) 
		? new $controllerClass($session, $args) 
		: null;

//load the view
$view = new $viewClass($session, $args, $controller);

//execute the view
$view->$action($args['id'] ?? null);

//now give the view to the page template
$template = $view->template ?? 'page';
if($session->userLevel > NPDC_PUBLIC && $view->class==="edit"){
	$extraJS = '<script type="text/javascript" src="'.BASE_URL.'/js/external/jHtmlArea/jHtmlArea-0.8.min.js?v='.APP_BUILD.'"></script><script type="text/javascript" src="'.BASE_URL.'/js/editor'.(NPDC_DEV ? '' : '.min').'.js?v='.APP_BUILD.'"></script>';
	$extraCSS = '<link rel="stylesheet" type="text/css" href="'.BASE_URL.'/css/jHtmlArea/jHtmlArea.css?v='.APP_BUILD.'" />';
}
require dirname(__FILE__).'/../private/npdc/template/'.$template.'.tpl.php';

//remove errors from the session
unset($_SESSION['errors']);
var_dump($args);