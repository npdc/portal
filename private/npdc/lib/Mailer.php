<?php

/**
 * Mail lib, single place to send mail from
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\lib;

class Mailer {
    private $mail;
    
    /**
     * Constructor
     *
     * @param string $fromName Name to be used as sender
     * @param string $replyMail Mailaddress where replies have to be sent
     */
    public function __construct($fromName = null, $replyMail = null) {
        $this->mail = new \PHPMailer\PHPMailer\PHPMailer();
        $this->mail->IsSMTP();
        $this->mail->setFrom(
            \npdc\config::$mail['from'],
            is_null($fromName) 
                ? \npdc\config::$siteName 
                : $fromName . ' through ' . \npdc\config::$siteDomain
        );
        $this->mail->addReplyTo(
            is_null($replyMail) 
                ? \npdc\config::$mail['contact'] 
                : $replyMail
        );
        if (!empty(\npdc\config::$mail['host'])) {
            $this->mail->isSMTP();
            $this->mail->Host = \npdc\config::$mail['host'];
            $this->mail->Port = \npdc\config::$mail['port'];
            if (isset(\npdc\config::$mail['SMTPSecure'])) {
                $this->mail->SMTPSecure = \npdc\config::$mail['SMTPSecure'];
            }
            if (\npdc\config::$mail['user'] ?? false) {
                $this->mail->SMTPAuth = true;
                $this->mail->Username = \npdc\config::$mail['user'];
                $this->mail->Password = \npdc\config::$mail['pass'];
            }
        }
    }
    
    /**
     * Set receiver details
     *
     * @param string $mail Address to send message to
     * @param string|null $name Name of receiver (if known)
     * @return void
     */
    public function to($mail, $name = null) {
        if (is_null($name)) {
            $this->mail->addAddress($mail);
        } else {
            $this->mail->addAddress($mail, $name);
        }
    }
    
    /**
     * Set subject
     *
     * @param string $subject Mail subject
     * @return void
     */
    public function subject($subject) {
        $this->mail->Subject = $subject;
    }
    
    /**
     * Set body, plain text only
     *
     * @param string $text Message text
     * @return void
     */
    public function text($text) {
        $this->mail->Body = $text;
    }
    
    /**
     * Do send the mail
     *
     * @return void
     */
    public function send() {
			if (!NPDC_DEV) {
					echo 'Starting to send';

var_dump(            $this->mail->send());
        }
    }
}
