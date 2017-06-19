<?php

/**
 * contact controller
 */

namespace npdc\controller;

class Contact {
	private $state = 'form';
	public $person;
	public $formId;
	public $form;
	public $model;
	private $formController;
	
	/**
	 * 
	 * @param object $session
	 * @param array $args
	 */
	public function __construct($session, $args){
		if(count($args) > 1){
			$this->model = new \npdc\model\Person;
			$this->person = $this->model->getById($args[1]);
			$this->name = $this->person['name'];
			$this->to = $this->person['mail'];
			$this->formId = str_replace('/', '_', $_SERVER['REQUEST_URI']);
		} else {
			$this->name = 'General mailbox of '. \npdc\config::$siteName;
			$this->to = \npdc\config::$siteMail;
			$this->formId = 'contact';
		}
		$this->formController = new \npdc\controller\Form($this->formId);
		$this->form = $this->formController->getForm('contact');
		$this->form->action = $_SERVER['REQUEST_URI'];
		if(array_key_exists('name', $_POST)){
			$this->formController->doCheck();
			if($this->formController->ok){
				$this->doSend();
				$_SESSION[$this->formId]['state'] = 'sent';
			} else {
				$_SESSION[$this->formId]['state'] = 'form';
			}
			header('Location: '.$_SERVER['REQUEST_URI']);
			die();
		} else {
			if($this->state === 'form'){
				if(!array_key_exists($this->formId, $_SESSION)){
					$_SESSION[$this->formId] = [
						'data'=>['name'=>null, 'mail'=>null, 'message'=>null],
						'errors'=>[]];
				}
			}
		}
	}
	
	/**
	 * sends the mail
	 */
	private function doSend(){
		$mail = new \npdc\lib\Mailer($_POST['name'], $_POST['mail']);
		$mail->to($this->to, $this->name);
		$mail->subject(empty($_POST['subject']) ? 'Message through '.\npdc\config::$siteDomain : $_POST['subject']);
		$mail->text($_POST['message']."\r\n-----\r\n".'This message was sent by '.$_POST['name'].' ('.$_POST['mail'].') on '.date('Y-m-d G:i:s').' through '.\npdc\config::$siteDomain);
		$mail->send();
		
		$ccMail = new \npdc\lib\Mailer();
		$ccMail->to($_POST['mail'], $_POST['name']);
		$ccMail->subject(empty($_POST['subject']) ? 'Copy of your message through '.\npdc\config::$siteDomain : $_POST['subject'].' (copy of your message)');
		$ccMail->text('The following message was sent on your behalf to '.$this->name.' on '.date('Y-m-d G:i:s').' through '.\npdc\config::$siteDomain.':'."\r\n".$_POST['message']);
		$ccMail->send();
		
		$_SESSION['notice'] = 'Thank you for your message. A copy has just been sent to your mailbox.';
		/* Set session var to sent, session will be cleared in the view */
		$_SESSION[$this->formId]['state'] = 'sent';
	}
}