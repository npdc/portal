<?php

$configFile = __DIR__.'/../config.php';
if(file_exists($configFile)){
	include($configFile);
} else {
	die('Configuration file not found, please create from config.template.php in private');
}