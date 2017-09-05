<?php

namespace npdc\lib;

class Push {
	public static function send($title, $url, $text=null){
		if(!NPDC_DEV){
			$curl = 'https://maker.ifttt.com/trigger/'.\npdc\config::$ifttt['event'].'/with/key/'.\npdc\config::$ifttt['token'];
			$ch = curl_init($curl);
			$xml = 'value1='.html_entity_decode(filter_var($title, FILTER_SANITIZE_STRING)).'&value2='.$url;
			if(!empty($text)){
				$xml .= '&value3='.html_entity_decode(filter_var($text, FILTER_SANITIZE_STRING));
			}

			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

			curl_exec($ch);
			curl_close($ch);
		}
	}
}