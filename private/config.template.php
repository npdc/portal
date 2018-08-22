<?php

/**
 * Configuration settings
 */

namespace npdc;

class config {
	//basic site config
	public static $siteName = 'National Polar Data Center';
	public static $siteDomain = 'example.com';
	
	//db connection
	public static $db = [
		'type' => 'mysql',//currently permitted: pgsql, mysql
		'host' => '127.0.0.1',
		'user' => 'database user',
		'pass' => 'database password',
		//'port' => 3306,//only needed with db on non-standard port
		'name' => 'database name',//name of the database
		'search_path' => 'public'//only for pgsql
	];
	
	
	public static $mail = [
		'contact' => 'info@example.com',//mail adress where main contact form is sent to
		'from' => 'noreply@example.com',
		'host' => '',//SMTP host
		'port' => '25',
		'SMTPSecure' => '',
		'user' => '',//smtp user
		'pass' =>'' //smtp pass
	];
	
	//GCMD login, needed for sync of vocabularies and records
	public static $gcmd = [
		'user' => '',
		'pass' => ''
	];
	
	
	public static $social =[
		'twitter'=>'',//twitter name without @
		'twitter_in_head'=>true //display link to twitter in top of each page
	];

	//path to location for data files and download zips, recommend outside webroot, no trailing slash
	public static $fileDir = DOCROOT_APP.'/../data';//dir to store data
	public static $downloadDir = DOCROOT_APP.'/../download';//dir to store downloadable files
	
	//the ip('s) from which debugging is allow without logging in, netmask allowed
	public static $debugFrom = [];
	
	public static $sessionExpire = 24;//number of minutes of inactivity after which session expires
	
	public static $allowRegister = true;//can a visitor create an account
	
	public static $resetExpiryHours = 24;//how long should a reset link remain valid
	public static $newExpiryHours = 48;//how long should a new account link remain valid
	
	public static $passwordMinLength = 8;
	
	public static $showNew = 5;//number of new items to show on front page

	public static $surname_regex = '/(?<f>(v[ao]n )?(de[rn]? )?(?<l>[A-Za-zÀ-ÖØ-öø-ÿ\-]*))$/i';//Regex giving a f (full surname) and l (surname without the prefixes) used for levenshtein ratios
	public static $levenshtein_ratio_person = 51;//(int) precentage similarity to consider a person in the database the same as the search string
	
	//if a part doesn't contain data yet you can disabled the part, admins keep access to these parts
	public static $partEnabled = [
		'dataset'=>true,
		'publication'=>true,
		'project'=>true
	];
	
	public static $reviewBeforePublish = false;
	
	//used to send a message to twitter
	public static $ifttt = [
		'token'=>'',
		'event'=>''
	];
	
	public static $loginEnabled = true;//UPDATE message below BEFORE taking site OFFLINE
	public static $loginDisabledMessage = 'Login is disabled because of maintenance. We expect to be finished Friday 12 May at 11 am.';

	public static $cronKey = '';//a random key to prevent cron runs from unauthorized persons

	public static $dataCenter = [];//default datacenter(s) for dif record if no other datacenter provided as <organization_id>=>[<person_id(s)>]

	public static $UUIDNamespace = '00000000-0000-0000-000000000000';//namespace UUID to use for UUID generation. Use a v1 or v4 UUID for this.

	public static $useReCaptcha = false;
	public static $reCaptcha = [
		'siteKey'=>'',
		'secretKey'=>''
	];

	public static $extraHeader = '';//extra code for in head section of page, for example usage trackers
}