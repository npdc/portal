<?php

namespace npdc\lib;

class Mailer {
	private $mail;
	public function __construct($fromName = null, $replyMail = null) {
		$this->mail = new \PHPMailer();
		$this->mail->setFrom(\npdc\config::$siteFromMail, is_null($fromName) ? \npdc\config::$siteName : $fromName .' through '. \npdc\config::$siteDomain);
		$this->mail->addReplyTo(is_null($replyMail) ? \npdc\config::$siteMail : $replyMail);
		if(property_exists('\npdc\config','mail')){
			$this->mail->isSMTP();
			$this->mail->Host = \npdc\config::$mail['host'];
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