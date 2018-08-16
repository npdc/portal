<?php

/**
 * Wrapper for curl request
 * 
 * Sets many usefull parameters without having to do this at every place where curl is used
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */
namespace npdc\lib;

class CurlWrapper{
	private $ch;

	/**
	 * Constructors
	 *
	 * @param array|null $headers additional http headers
	 */
	public function __construct($headers = null) {
		$this->ch = curl_init();
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		$http_headers = array(
			'User-Agent: NPDC', // Any User-Agent will do here
		);
		if(is_array($headers)){
			$http_headers = array_merge($http_headers, $headers);
		}
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $http_headers);
		curl_setopt($this->ch, CURLOPT_AUTOREFERER, true); 
	}

	/**
	 * Destructor
	 */
	public function __destruct() {
		curl_close($this->ch);
	}

	/**
	 * Provide authentication when needed
	 *
	 * @param string $username Username for request
	 * @param string $password Password for request
	 * @return void
	 */
	public function httpauth($username, $password){
		curl_setopt($this->ch, CURLOPT_USERPWD, "$username:$password");
		curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	}

	/**
	 * Request url and return result
	 *
	 * @param string $url the url to request
	 * @return string result
	 */
	public function get($url){
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($this->ch, CURLOPT_HEADER, false);
		curl_setopt($this->ch, CURLOPT_URL, $url);
		return curl_exec($this->ch);
	}

	/**
	 * Find where url is redirecting
	 *
	 * @param string $url the url
	 * @return string the url where $url redirects to
	 */
	public function getRedirect($url){
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($this->ch, CURLOPT_HEADER, true);
		curl_setopt($this->ch, CURLOPT_URL, $url);
		
		$http_data = curl_exec($this->ch); //hit the $url
		$curl_info = curl_getinfo($this->ch);
		$headers = substr($http_data, 0, $curl_info["header_size"]); //split out header
		preg_match("!\r\n(?:Location|URI): *(.*?) *\r\n!", $headers, $matches);
		return $matches[1];
	}
	
	/**
	 * Get status of curl request
	 *
	 * @return mixed
	 */
	public function status(){
		return curl_getinfo($this->ch);
	}
}
