<?php

/**
 * Generate metadata record in DIF 10 format
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\lib;

class Dif {
    private $xml;

    /**
     * Start xml
     */
    public function __construct() {
        $this->xml =  new \SimpleXMLElement(
            '<?xml version="1.0" encoding="utf-8"?>'
            . '<DIF xmlns="http://gcmd.gsfc.nasa.gov/Aboutus/xml/dif/" '
            . 'xmlns:dif="http://gcmd.gsfc.nasa.gov/Aboutus/xml/dif/" '
            . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
            . 'xsi:schemaLocation="http://gcmd.gsfc.nasa.gov/Aboutus/xml/dif/ '
            . 'https://gcmd.gsfc.nasa.gov/Aboutus/xml/dif/dif_v10.2.xsd"></DIF>'
        );
    }
    
    /**
     * parse array of values and add to xml, can be called recursive
     *
     * @param array $data data to add
     * @param object $element xml-element to add data to
     * @return void
     */
    public function parseArray($data, $element=null) {
        if ($element === null) {
            $element = &$this->xml;
        }
        foreach ($data as $key=>$value) {
            if (is_array($value)) {
                if (is_numeric(array_keys($value)[0])) {
                    foreach ($value as $v) {
                        if (is_array($v)) {
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

    /**
     * Make value xml safe
     *
     * @param string $value value to make safe
     * @return string xml safe value
     */
    private function parseValue($value) {
        return htmlspecialchars(html_entity_decode($value));
    }
    
    /**
     * give the dif as xml file
     *
     * @return void
     */
    public function output() {
        header('Content-Type: application/xml');
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($this->xml->asXML());
        echo $dom->saveXML();
    }
}