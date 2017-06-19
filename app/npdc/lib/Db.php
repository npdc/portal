<?php

/**
 * database connection class 
 */

namespace npdc\lib;

use \PDO;

class Db {
	private static $fpdo = NULL;
	private static $instance = NULL;
	
	private static $defaultPorts = ['pgsql'=>5432, 'mysql'=>'3306'];
	
	public static $sortByRecordStatus = "CASE 
		WHEN record_status='published' THEN 1
		WHEN record_status IN ('draft', 'submitted') THEN 2
		WHEN record_status='archived' THEN 3
		END";
	
	private function __construct() {}
	
	private function __clone() {}
	
	private static function getInstance(){
		if(!isset(self::$instance)){
			$db = \npdc\config::$db;
			$pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
			if($db['type'] === 'mysql'){
				$pdo_options[PDO::ATTR_EMULATE_PREPARES] = false;
			}
			try {
				$instance = new PDO($db['type'].':host='.$db['host'].';dbname='.$db['name'].';port='.($db['port'] ?? self::$defaultPorts[$db['type']]), $db['user'], $db['pass'], $pdo_options);
			} catch(PDOException $e) {
				echo 'Error';echo $e->getMessage();
				die();
			}
			switch($db['type']){
				case 'pgsql':
					$searchPath = $db['search_path'] ?? 'public';
					$stmt = $instance->prepare('SET search_path TO '.$searchPath);
					$stmt->execute();
					break;
				case 'mysql':
					$stmt = $instance->prepare('SET sql_mode=PIPES_AS_CONCAT');
					$stmt->execute();
					$stmt = $instance->prepare('SET NAMES utf8');
					$stmt->execute();
					$stmt = $instance->prepare('SET CHARACTER SET utf8');
					$stmt->execute();
					break;
			} 
			self::$instance = $instance;
		}
			
		return self::$instance;
	}
	
	public static function getPDO(){
		$instance = self::getInstance();
		return $instance;
	}
	
	public static function getFPDO(){
		if(!isset(self::$fpdo)){
			$instance = self::getInstance();
			
			$structure = new \FluentStructure('%s_id');
			self::$fpdo = new \FluentPDO($instance, $structure);
			
			if(NPDC_DEV && NPDC_DB_DEBUG){
				self::$fpdo->debug = function($BaseQuery) {
					echo "query: " . $BaseQuery->getQuery(false) . "<br/>";
					echo "parameters: " . implode(', ', $BaseQuery->getParameters()) . "<br/>";
					echo "rowCount: " . $BaseQuery->getResult()->rowCount() . "<br/><br/>";
				};
			}
		}
		return self::$fpdo;
	}
	
	public static function insertReturnId($tbl, $data){
		switch(\npdc\config::$db['type']){
			case 'pgsql':
				$keys = [];
				$values = [];
				foreach($data as $key=>$value){
					$keys[] = $key;
					$values[':'.$key] = $value;
				}
				$q = self::getInstance()->prepare('INSERT INTO '.$tbl.'('.implode(',', $keys).') VALUES ('.implode(', ', array_keys($values)).') RETURNING '.$tbl.'_id');
				foreach($values as $key=>$value){
					if (empty($value)) {
						$data_type = PDO::PARAM_NULL;
						$value = "NULL";
					} elseif (is_float($value)) {
						$data_type = PDO::PARAM_STR;
					} elseif (is_numeric($value)) {
						$data_type = PDO::PARAM_INT;
					} else {
						$data_type = PDO::PARAM_STR;
					}
					$q->bindValue($key, $value, $data_type);
				}
				$q->execute();
				return $q->fetch(PDO::FETCH_ASSOC)[$tbl.'_id'];
			case 'mysql':
				return self::getFPDO()->insertInto($tbl, $data)->execute();
		}
	}
	
	public static function executeQuery($query){
		$q = self::getInstance()->prepare($query);
		return $q->execute();
	}
}
