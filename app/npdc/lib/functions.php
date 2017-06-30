<?php

/**
 * some basic functions in use throughout the site
 */

/**
 * get the filename of the a class
 * @param string $className the name of the class
 * @return string filename
 */
function get_class_file($className){
	return substr(dirname(__FILE__),0,-4).'/'
			.str_replace('\\', '/', substr($className,5))
			.'.php';
}

/**
 * generate a random string for use in captcha's or passwords
 * @param integer $length the number of characters that should be in the string
 * @param boolean $special use specicial charactars (default: false)
 * @return strong
 */
function generateRandomString($length, $special = false){
	$characters = str_split('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnprstuvwxyz2345689');
	if($special){
		$characters = array_merge($characters
				, explode(" ", "_ ! # $ % & / ( ) = ? + * ~ ^ [ ] { } - :")
			);
	}
	$max = count($characters)-1;
	$string = '';
	for ($i = 0; $i < $length; $i++) {
		$string .= $characters[rand(0, $max)];
	}
	return $string;
}

/**
 * match ip with cidr
 * @param string $ip an ip adres
 * @param string $cidr a cidr
 * @return boolean
 */
function cidr_match($ip, $cidr)
{
    list($subnet, $mask) = explode('/', $cidr);

    if ((ip2long($ip) & ~((1 << (32 - $mask)) - 1) ) == ip2long($subnet))
    { 
        return true;
    }

    return false;
}

function formatBytes($size, $precision = 2) {
    $base = log($size, 1024);
    $suffixes = array('', 'K', 'M', 'G', 'T', 'P');   

    return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)].'B';
}

function convertToBytes($from){
    $number=(int)$from;
    switch(substr($from, strlen($number))){
        case 'KB':
		case 'K':
            return $number*1024;
        case 'MB':
		case 'M':
            return $number*pow(1024,2);
        case 'GB':
		case 'G':
            return $number*pow(1024,3);
        case 'TB':
		case 'T':
            return $number*pow(1024,4);
        case 'PB':
		case 'P':
            return $number*pow(1024,5);
        default:
            return $from;
    }
}

function array_key_exists_r($needle, $haystack){
    $result = array_key_exists($needle, $haystack);
    if (!$result){
		foreach ($haystack as $v) {
		    if (is_array($v)) {
		        $result = array_key_exists_r($needle, $v);
				if ($result){
					break;
				}
			}
		}
    }
    return $result;
}


function maxFileUpload() {
    //select maximum upload size
    $max_upload = convertToBytes(ini_get('upload_max_filesize'));
    //select post limit
    $max_post = convertToBytes(ini_get('post_max_size'));
    //select memory limit
    $memory_limit = convertToBytes(ini_get('memory_limit'));
    // return the smallest of them, this defines the real limit
    return min($max_upload, $max_post, $memory_limit);
}

function getProtocol(){
	return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
}

function checkUrl($url, $protocol = 'http://'){
	return parse_url($url, PHP_URL_SCHEME) === null ?
		$protocol . $url : $url;
}

function parseLon($lon){
	while($lon > 180){
		$lon -= 360;
	}
	while($lon < -180){
		$lon += 360;
	}
	return $lon;
}