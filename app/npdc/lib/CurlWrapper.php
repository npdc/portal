<?php

namespace npdc\lib;

class CurlWrapper{
	private $ch;
	public function __construct() {
		$this->ch = curl_init();
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		$http_headers = array(
			'User-Agent: NPDC', // Any User-Agent will do here
		);
		//curl_setopt($this->ch, CURLOPT_HEADER, true);
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $http_headers);
	}
	public function __destruct() {
		curl_close($this->ch);
	}

	public function httpauth($username, $password){
		curl_setopt($this->ch, CURLOPT_USERPWD, "$username:$password");
		curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	}
	public function get($url){
		curl_setopt($this->ch, CURLOPT_URL, $url);
		return curl_exec($this->ch);
	}
	
	public function status(){
		return curl_getinfo($this->ch);
	}
}
