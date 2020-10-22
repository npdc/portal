<?php

/**
 * some basic functions in use throughout the site
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

/**
 * get the filename of the a class
 * @param string $className the name of the class
 * @return string filename
 */
function get_class_file($className) {
    return substr(dirname(__FILE__),0,-4) . '/'
        . str_replace(
            '\\',
            '/',
            (substr($className,0,5) === 'npdc\\' 
                ? substr($className,5) 
                : $className
            )
        )
        .'.php';
}

/**
 * generate a random string for use in captcha's or passwords
 * @param integer $length the number of characters that should be in the string
 * @param boolean $special use specicial charactars (default: false)
 * @return string the random string
 */
function generateRandomString($length, $special = false) {
    $characters = str_split(
        'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnprstuvwxyz2345689'
    );
    if ($special) {
        $characters = array_merge(
            $characters
            , str_split(
                "_!#$%&/()=?+*~^[]{}-:"
            )
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
 * @return boolean ip in cidr
 */
function cidr_match($ip, $cidr) {
    list($subnet, $mask) = explode('/', $cidr);
    if ((ip2long($ip) & ~((1 << (32 - $mask)) - 1) ) == ip2long($subnet))
    { 
        return true;
    }
    return false;
}

/**
 * format file size in bytes into logical larger unit
 *
 * @param integer $size filesize
 * @param integer $precision number of decimals to give
 * @return string formatted filesize including unit
 */
function formatBytes($size, $precision = 2) {
    $base = log($size, 1024);
    $suffixes = array('', 'K', 'M', 'G', 'T', 'P');
    return round(
            pow(1024, $base - floor($base)),
            $precision
        ) 
        . ' ' 
        . $suffixes[floor($base)].'B';
}

/**
 * Convert filesize in larger unit to bytes
 *
 * @param string $from filesize
 * @return integer filesize in bytes
 */
function convertToBytes($from) {
    $number = (float)$from;
    $powers = [
        'K'=>1,
        'M'=>2,
        'G'=>3,
        'T'=>4,
        'P'=>5
    ];
    $power = strtoupper(
        substr(
            $from,
            strlen($number),
            1
        )
    );
    return $number * pow(1024, $powers[$power]);
}

/**
 * recursive array_key_exists
 *
 * @param string $needle key to find
 * @param array $haystack array to find the key in
 * @return boolean was key found
 */
function array_key_exists_r($needle, $haystack) {
    $result = is_array($haystack) 
        ? array_key_exists($needle, $haystack)
        : property_exists($haystack, $needle);
    if (!$result) {
        foreach ($haystack as $v) {
            if (is_array($v)) {
                $result = array_key_exists_r($needle, $v);
                if ($result) {
                    break;
                }
            }
        }
    }
    return $result;
}

/**
 * Get maximum file upload size based on the relevant parameters
 * 
 * Returns lowest of upload_max_filesize, post_max_size and memory_limit
 *
 * @return integer maximmum number of bytes to upload
 */
function maxFileUpload() {
    //select maximum upload size
    $max_upload = convertToBytes(
        ini_get('upload_max_filesize')
    );
    //select post limit
    $max_post = convertToBytes(
        ini_get('post_max_size')
    );
    //select memory limit
    $memory_limit = convertToBytes(
        ini_get('memory_limit')
    );
    // return the smallest of them, this defines the real limit
    return min(
        $max_upload,
        $max_post,
        $memory_limit
    );
}

/**
 * determine if http or https is used
 *
 * @return string protocol
 */
function getProtocol() {
    return (
            (
                !empty($_SERVER['HTTPS']) 
                && $_SERVER['HTTPS'] !== 'off'
            )
            || $_SERVER['SERVER_PORT'] == 443
        )
        ? "https://"
        : "http://";
}

/**
 * Add protocol to url if no protocol is given
 *
 * @param string $url the url to check
 * @param string $protocol the protocol to add if none is provided
 * @return string The url with protocol
 */
function checkUrl($url, $protocol = 'http://') {
    return parse_url($url, PHP_URL_SCHEME) === null 
        ? $protocol . $url 
        : $url;
}

/**
 * Make longitude between -180 and 180 (inclusive)
 *
 * @param float $lon the longitude
 * @return float the corrected longitude
 */
function parseLon($lon) {
    while ($lon > 180) {
        $lon -= 360;
    }
    while ($lon < -180) {
        $lon += 360;
    }
    return $lon;
}

/**
 * Get the mime type the visitor wishes to have from available types
 *
 * @param array $mimeTypes list of available mime types
 * @return mixed either array of accepted mime type, string of best mime type or 
 *                  null
 */
function getBestSupportedMimeType($mimeTypes = null) {
    $acceptTypes = [];

    $accept = explode(
        ',',
        strtolower(
            str_replace(
                ' ',
                '',
                $_SERVER['HTTP_ACCEPT']
            )
        )
    );
    foreach ($accept as $a) {
        $q = 1;
        if (strpos($a, ';q=')) {
            list($a, $q) = explode(';q=', $a);
        }
        $AcceptTypes[$a] = $q;
    }
    arsort($AcceptTypes);

    if (!$mimeTypes) {
        return $AcceptTypes;
    }
    
    foreach ($AcceptTypes as $mime => $q) {
        if ($q > 0 && array_key_exists($mime, $mimeTypes)){
            return $mimeTypes[$mime];
        }
    }
    // no mime-type found
    return null;
}