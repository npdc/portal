<?php

/**
 * login controller
 * form processing is done in \npdc\lib\Login
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\controller;

class Login {
	public $formId;
	public $form;
	protected $model;
	public $record;
	
	/**
	 * Constructor
	 */
	public function __construct($session, $args){
		$this->model = new \npdc\model\Person();
		switch($args[1]){
			case 'reset':
				if(count($args) === 2){
					$this->formId = 'login_reset_password';
				} else {
					$key = $this->model->getPasswordReset($args[2]);
					if(count($key) === 0 || !password_verify($args[3], $key['code'])){
						return;
					}
					$this->formId = 'login_new_password';
				}
				break;
			default:
				$this->formId = 'login';
		}
		$this->formController = new \npdc\controller\Form($this->formId);
		$this->form = $this->formController->getForm($this->formId);
		
		if(array_key_exists('notice', $_GET)){
			switch($_GET['notice']){
				case 'expired':
					$this->form->fields->action->value = 'parent/submit';
					break;
				case 'login':
					$this->form->fields->action->value = 'parent';
					break;
			}
		}
		if(array_key_exists('formid', $_POST) && $_POST['formid'] === $this->formId){
			$this->formController->doCheck();
			if($this->formController->ok){
				$person = $this->model->getUser($_SESSION[$this->formId]['data']['mail']);
				switch($this->formId){
					case 'login':
						if(is_null($person) || !password_verify($_SESSION[$this->formId]['data']['password'], $person['password'])){
							$_SESSION[$this->formId]['errors']['mail'] = 'No user found with these details';
							$_SESSION[$this->formId]['errors']['password'] = 'No user found with these details';
							header('Location: '.BASE_URL.'/login');
							die();
						} else {
							$this->model->expirePasswordResetLogin($person['person_id']);
							$_SESSION['user']['id'] = $person['person_id'];
							switch($_SESSION[$this->formId]['data']['action']){
								case 'parent/submit':
									echo 'Saving...<script language="javascript" type="text/javascript">
	window.parent.waitingForm.submit();
	</script>';
									break;
								case 'parent':
									echo '<body class="user">Logging in...<script language="javascript" type="text/javascript">
	window.parent.closeOverlay()
	</script></body>';
									break;
								default:
									$loc = empty($_SESSION[$this->formId]['data']['referer']) ? 'login' : $_SESSION[$this->formId]['data']['referer'];
									if($loc === 'account'){
										header('Location: '.BASE_URL.'/account');
									} else {
										header('Location: '.BASE_URL.'/overlay/editor?u='
										.(strpos($loc, 'login/') !== false ? BASE_URL.'/' : $loc)
											);
									}
									break;
							}
							unset($_SESSION[$this->formId]);
							die();		
						}
						break;
					case 'login_reset_password':
						if(!empty($person)){
							$this->sendPasswordResetLink($person);
						}
						unset($_SESSION[$this->formId]);
						$_SESSION['notice'] = 'If an account exists for the provided mail adress a reset link has been sent to the mail address';
						header('Location: ../login');
						die();
						break;
					case 'login_new_password':
						if(strlen($_POST['password']) < \npdc\config::$passwordMinLength) {
							$_SESSION[$this->formId]['errors']['password'] = 'The new password is too short';
						} else {
							$this->model->updatePerson(['password'=>password_hash($_POST['password'], PASSWORD_DEFAULT)], $args[2]);
							$this->model->usePasswordReset($key['account_reset_id']);
							$_SESSION['notice'] = 'Your new password has been saved';
							unset($_SESSION[$this->formId]);
							header('Location: '.BASE_URL.'/?overlay=login');
							die();
						}
						break;
				}
			} 
		}
		
		/*update formfield*/
		switch($this->formId){
			case 'login_new_password':
				$this->form->action = $_SERVER['REQUEST_URI'];
				$person = $this->model->getById($args[2]);
				$this->form->fields->head->hint = 'Hi '.$person['name'].', please enter a new password below.';
				$this->form->fields->password->hint = 'The password has to be '. \npdc\config::$passwordMinLength.' characters or longer';
				break;
			default:
				$this->form->fields->referer->value = $_SESSION[$this->formId]['data']['referer'] ?? $_GET['referer'] ?? $_GET['u'] ?? $_SERVER['HTTP_REFERER'];
		}
	}
	
	/**
	 * Send password reset link
	 *
	 * @param array $person person record
	 * @param boolean $newAccount request came in via create account or now
	 * @return void
	 */
	protected function sendPasswordResetLink($person, $newAccount = false){
		
		$code = $this->model->requestPasswordReset(['person_id'=>$person['person_id']]);
		$message = 'Hi '.$person['name'].",\r\n\r\n"
			. 'Someone, hopefully you, requested a ';
		$link = "\r\n\r\n".$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].BASE_URL.'/login/reset/'.$person['person_id'].'/'.$code."\r\n\r\n";
		if($newAccount){
			$message .= 'new account for '.$person['mail'].' on '.\npdc\config::$siteDomain.'. Multiple accounts connected to a single mail address are not possible. In case you forgot your password you can set a new password using the following link:'.$link.'If you don\'t want to reset your password no action is needed.';
		} else {
			$message .= 'password reset for '.$person['mail'].' on '.\npdc\config::$siteDomain.'. You can set a new password using the following link:'.$link
			.'If you didn\'t request a password reset no action is needed.';
		}
		$message .= ' Your current password remains functional until the link in this message is used. This link will expire in '. \npdc\config::$resetExpiryHours.' hours or when you use your current password to login.'."\r\n\r\nKind regards,\r\n". \npdc\config::$siteName;
		
		$mail = new \npdc\lib\Mailer();
		$mail->to($person['mail'], $person['name']);
		$mail->subject('Password reset link for '.\npdc\config::$siteDomain);
		$mail->text($message);
		$mail->send();
	}
}