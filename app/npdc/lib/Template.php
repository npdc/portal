<?php

/**
 * helper for parsing templates
 */

namespace npdc\lib;

class Template {
	/**
	 * format a string using htmlentities and some specific code
	 * @param string $value
	 * @param string $type type of data
	 * @param object $data optional data from which value has to be taken
	 * @return type
	 */
	public static function printString($value, $type = null, $data = null){
		if(is_null($data)){
			$str = $value;
		} else {
			$str = $data->$value;
		}
		switch($type){
			case 'bool':
				$str = $str ? 'yes' : 'no';
				break;
			case 'date':
				$str = date('Y-m-d', strtotime($str));
				break;
		}
		return htmlentities($str, \ENT_COMPAT, 'UTF-8');
	}
}