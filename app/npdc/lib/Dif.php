<?php

namespace npdc\lib;

class Dif {
	private $xml;

	public function __construct(){
		$this->xml =  new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><DIF xmlns="http://gcmd.gsfc.nasa.gov/Aboutus/xml/dif/" xmlns:dif="http://gcmd.gsfc.nasa.gov/Aboutus/xml/dif/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://gcmd.gsfc.nasa.gov/Aboutus/xml/dif/ https://gcmd.gsfc.nasa.gov/Aboutus/xml/dif/dif_v10.2.xsd"></DIF>');
	}

	public function parseArray($data, $element = null){
		if($element === null){
			$element = &$this->xml;
		}
		foreach($data as $key=>$value){
			if(is_array($value)){
				if(is_numeric(array_keys($value)[0])){
					foreach($value as $v){
						if(is_array($v)){
							$this->parseArray($v, $element->addChild($key));
						} else {
							$element->addChild($key, $this->parseValue($v));
						}
					}
				} else {
					$this->parseArray($value, $element->addChild($key));
				}
			} else {
				$element->addChild($key, $this->parseValue($value));
			}
		}
	}

	private function parseValue($value){
		return htmlspecialchars($value);
	}
	
	public function output(){
		header('Content-Type: application/xml');
		$dom = new \DOMDocument('1.0');
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->loadXML($this->xml->asXML());
		echo $dom->saveXML();
	}
}