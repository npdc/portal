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

use \npdc\lib\Args;

$controllerName = ucfirst(Args::get('type'));
$action = Args::exists('id') || Args::exists('action') ? 'showItem' : 'showList';

//get the view
$controllerClass = 'npdc\\controller\\'.$controllerName;
$viewClass = 'npdc\\view\\'.$controllerName;

//if a controller/view is requested that doesn't exists or shouldn't be called directly use Page class.
if(in_array($controllerName, ['Base', 'Form']) || !file_exists(get_class_file($viewClass))){
	$viewClass = 'npdc\\view\\Page';
	$controllerName = 'Page';
	$controllerClass = 'npdc\\controller\\Page';
	$action = 'showItem';
	Args::set('id', Args::get('type'));;
}

//now load controller if it exists
$controller = (file_exists(get_class_file($controllerClass))) 
		? new $controllerClass($session) 
		: null;

//load the view
$view = new $viewClass($session, $controller);

//execute the view
$view->$action(Args::get('id') ?? null);

//now give the view to the page template
$template = $view->template ?? 'page';
if($session->userLevel > NPDC_PUBLIC && $view->class==="edit"){
	$extraJS = '<script type="text/javascript" src="'.BASE_URL.'/js/external/jHtmlArea/jHtmlArea-0.8.min.js?v='.APP_BUILD.'"></script><script type="text/javascript" src="'.BASE_URL.'/js/editor'.(NPDC_DEV ? '' : '.min').'.js?v='.APP_BUILD.'"></script>';
	$extraCSS = '<link rel="stylesheet" type="text/css" href="'.BASE_URL.'/css/jHtmlArea/jHtmlArea.css?v='.APP_BUILD.'" />';
}
require dirname(__FILE__).'/../private/npdc/template/'.$template.'.tpl.php';

//remove errors from the session
unset($_SESSION['errors']);