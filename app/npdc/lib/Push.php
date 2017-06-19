<?php

namespace npdc\lib;

class Push {
	public static function send($title, $url, $text=null){
		$curl = 'https://maker.ifttt.com/trigger/'.\npdc\config::$ifttt['event'].'/with/key/'.\npdc\config::$ifttt['token'];
		$ch = curl_init($curl);
		$xml = 'value1='.$title.'&value2='.$url;
		if(!empty($text)){
			$xml .= '&value3='.$text;
		}

		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

		curl_exec($ch);
		curl_close($ch);
	}
}