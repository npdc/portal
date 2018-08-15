<?php

namespace npdc\lib;

class Mailer {
	private $mail;
	public function __construct($fromName = null, $replyMail = null) {
		$this->mail = new \PHPMailer();
		$this->mail->setFrom(\npdc\config::$mail['from'], is_null($fromName) ? \npdc\config::$siteName : $fromName .' through '. \npdc\config::$siteDomain);
		$this->mail->addReplyTo(is_null($replyMail) ? \npdc\config::$mail['contact'] : $replyMail);
		if(!empty(\npdc\config::$mail['host'])){
			$this->mail->isSMTP();
			$this->mail->Host = \npdc\config::$mail['host'];
			$this->mail->Port = \npdc\config::$mail['port'];
			$this->mail->SMTPSecure = \npdc\config::$mail['SMTPSecure'];
			if(\npdc\config::$mail['user'] ?? false){
				$this->mail->SMTPAuth = true;
				$this->mail->Username = \npdc\config::$mail['user'];
				$this->mail->Password = \npdc\config::$mail['pass'];
			}
		}
	}
	
	public function to($mail, $name = null){
		if(is_null($name)){
			$this->mail->addAddress($mail);
		} else {
			$this->mail->addAddress($mail, $name);
		}
	}
	
	public function subject($subject){
		$this->mail->Subject = $subject;
	}
	
	public function text($text){
		$this->mail->Body = $text;
	}
	
	public function send(){
		$this->mail->send();
	}
}