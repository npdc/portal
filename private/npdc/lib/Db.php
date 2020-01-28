<?php

/**
 * database connection class 
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\lib;

use \PDO;

class Db {
	private static $dsql = NULL;
	private static $instance = NULL;
	
	private static $defaultPorts = ['pgsql'=>5432, 'mysql'=>'3306'];
	
	public static $sortByRecordStatus = "CASE 
		WHEN record_status='published' THEN 1
		WHEN record_status IN ('draft', 'submitted') THEN 2
		WHEN record_status='archived' THEN 3
		END";
	
	private function __construct() {}
	
	private function __clone() {}
	
	/**
	 * Create connection to database
	 *
	 * @return object database connection
	 */
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
	
	public static function getDSQLcon(){
		if(!isset(self::$dsql)){
			self::$dsql = \atk4\dsql\Connection::connect(self::getInstance());
		}
		return self::$dsql->dsql();
	}
	
	/**
	 * Insert a record and return the id of the new record
	 *
	 * @param string $tbl the table to insert into
	 * @param array $data the data to insert
	 * @param bool $returnId (optional) return id, default: false
	 * @return integer the id of the new record
	 */
	public static function insert($tbl, $data, $returnId = false){
		$r = self::getDSQLcon()->dsql()->table($tbl)->set($data)->insert();
		if($returnId){
			$q = self::getDSQLcon()->dsql()->table($tbl);
			foreach($data as $key=>$val){
				if(empty($val)){
					$q->where($key.' IS NULL');
				} else {
					$q->where($key, $val);
				}
			}
			return intval($q->order($tbl.'_id DESC')->get()[0][$tbl.'_id']);
		} else {
			return $r;
		}
	}

	private static function _getRecord($table, $record){
		$q = self::getDSQLcon()
			->table($table);
		if(is_array($record)){
			foreach($record as $key=>$val){
				if(is_null($val)){
					$q->where($key.' IS NULL');	
				} else {
					$q->where($key, $val);
				}
			}
		} elseif(is_numeric($record)) {
			$q->where($table.'_id', $record);
		} else {
			die('Illegal record selector');
		}
		return $q;
	}
	/**
	 * Perform update query
	 *
	 * @param string $table the table
	 * @param integer|array $record the primary key value
	 * @param array $data the new data
	 * @return void
	 */
	public static function update($table, $record, $data){
		$q = self::_getRecord($table, $record)
			->set($data)
			->update();
	}
	
	/**
	 * get a record by id or multiple fields
	 *
	 * @param string $tbl table
	 * @param integer|array $record the primary key value
	 * @return array record
	 */
	public static function get($tbl, $record){
		return self::_getRecord($tbl, $record)->get()[0] ?? false;
	}

	/**
	 * Execute arbitrary query
	 * 
	 * For queries that are hard or impossible to build using dsql
	 *
	 * @param string $query
	 * @return mixed
	 */
	public static function executeQuery($query){
		$q = self::getInstance()->prepare($query);
		return $q->execute();
	}

	/**
	 * Provide on clause for joining extra tables to project, dataset or publication
	 *
	 * @param string $ctype content type (and main table)
	 * @param string $joined other table in join
	 * @return Query 
	 */
	public static function joinVersion($ctype, $joined, $includeId = true){
		$q =self::getDSQLcon()->andExpr();
		if($includeId){
			$q->where($ctype.'.'.$ctype.'_id = '.$joined.'.'.$ctype.'_id');
		}
		return $q->where($ctype.'.'.$ctype.'_version >= '.$ctype.'_version_min')
			->where(self::getDSQLcon()->orExpr()
				->where($ctype.'_version_max IS null')
				->where($ctype.'_version_max >= '.$ctype.'.'.$ctype.'_version')
		);
	}

	/**
	 * Provide version selector
	 *
	 * @param string $ctype content type
	 * @param integer $id id of the record (can be ommitted, actually version can be ommitted, in that case id will be used as version)
	 * @param integer $version version number
	 * @return void
	 */
	public static function selectVersion($ctype, $id, $version = null){
		$q = self::getDSQLcon()->andExpr();
		if(empty($version)){
			$version = $id;
		} else {
			$q->where($ctype.'_id', $id);
		}
		$q->where($ctype.'_version_min', '<=', $version)
			->where(self::getDSQLcon()->orExpr()
				->where($ctype.'_version_max IS null')
				->where($ctype.'_version_max', '>=', $version)
		);
		return $q;
	}
}