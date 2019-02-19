<?php

/**
 * Url argument parser
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

 namespace npdc\lib;

class Args {
	private static $args = NULL;

	private static function parse(){
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
					. ')?'//get extension
					. '(\/(?P<file>[a-z0-9_]{1,}))?'
					. '(\.(?P<ext>[a-z0-9]{1,}))?'
				. ')'
			. ')?'
			. '(\?(.*))?'
			. '$';
		
		preg_match('/'.$pattern.'/i',str_replace('%20', ' ', $url), self::$args);
		foreach(self::$args as $key=>$val){
			if(is_numeric($key) || $val === ''){
				unset(self::$args[$key]);
			}
		}
		if(array_key_exists('search',self::$args)){
			self::$args['type'] = 'search';
			unset(self::$args['search']);
		}
		
		if(array_key_exists('logintype', self::$args)){
			self::$args['type'] = self::$args['logintype'];
			if(array_key_exists('loginid', self::$args)){self::$args['id'] = self::$args['loginid'];}
		}
		
		define('NPDC_OUTPUT', self::$args['ext'] ?? getBestSupportedMimeType(['text/html'=>'html', 'application/xhtml+xml'=>'html', 'text/xml'=>'xml', 'application/xml'=>'xml']) ?? 'html');
		
		if(array_key_exists('uuid', self::$args)){
			if(\Lootils\Uuid\Uuid::isValid(self::$args['uuid'])){
				if(strpos('-', self::$args['uuid']) === false){
					self::$args['uuid'] = preg_replace("/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/i", "$1-$2-$3-$4-$5", self::$args['uuid']);
				}
				foreach(array_key_exists('uuidtype', self::$args) ? [self::$args['uuidtype']] : ['dataset', 'project', 'publication'] as $cType){
					$mName = 'npdc\\model\\'.ucfirst($cType);
					$m = new $mName();
					$r = $m->getByUUID(self::$args['uuid']);
					if($r !== false){
						self::$args = array_merge(['type'=>$cType, 'id'=>$r[$cType.'_id'], 'version'=>$r[$cType.'_version']], self::$args);
						break;
					}
				}
			}
			if(!array_key_exists('type', self::$args)){
				self::$args['type'] = self::$args['uuidtype'] ?? '404';
				self::$args['id'] = 0;
			}
		}
		if(empty(self::$args['type'])){
			self::$args['type'] = 'front';
		}
	}

	public static function exists($arg){
		if(!isset(self::$args)){
			self::parse();
		}
		return array_key_exists($arg, self::$args);
	}

	public static function get($arg){
		if(!isset(self::$args)){
			self::parse();
		}
		return array_key_exists($arg, self::$args) ? self::$args[$arg] : null;
	}

	public static function getAll(){
		if(!isset(self::$args)){
			self::parse();
		}
		return self::$args;
	}

	public static function set($arg, $value){
		if(!isset(self::$args)){
			self::parse();
		}
		self::$args[$arg] = $value;
	}
}