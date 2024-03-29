<?php

/**
 * Register new account
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

namespace npdc\controller;

class Register extends Login{
    public $formId;
    public $form;
    protected $model;
    public $record;
    
    /**
     * Constructor
     *
     * @param object $session login information
     *
     */
    public function __construct($session) {
        $this->session = $session;
        $this->model = new \npdc\model\Person();
        if (!\npdc\lib\Args::exists('loginkey')) {
            $this->formId = 'register';
        } else {
            $this->record = $this->model->getPasswordNew(
                \npdc\lib\Args::get('loginid')
            );
            if (
                count($this->record) === 0
                || !password_verify(
                    \npdc\lib\Args::get('loginkey'),
                    $this->record['code']
                )
            ) {
                return;
            }
            $this->formId = 'register_create';
        }
        $this->formController = new \npdc\controller\Form($this->formId);
        $this->form = $this->formController->getForm($this->formId);
        if ($this->formId === 'register_create') {
            $persons = $this->model->getByMail($this->record['mail']);
            $this->form->fields->person->options = [];
            switch (count($persons)) {
                case 0:
                    $this->form->fields->person->disabled = true;
                    $this->form->fields->name->disabled = false;
                    break;
                case 1:
                    $this->form->fields->person->value = $persons[0]['person_id'];
                    $this->form->fields->person->type = 'hidden';
                    $this->form->fields->head->hint .= '<br/>Your account will '
                        . 'be linked to the details of \'<a href="' . BASE_URL
                        . '/contact/' . $persons[0]['person_id'] . '" target='
                        . '"_blank"><strong>' . $persons[0]['name']
                        . '</strong></a>\' since this record uses the same mail'
                        . ' address as you used to request an account. If this '
                        . 'is not right please <a href="'.BASE_URL.'/contact">'
                        .' contact the NPDC</a>';
                    break;
                default:
                    foreach ($persons as $person) {
                        $this->form->fields->person
                            ->options[$person['person_id']] = $person['name'];
                    }
            }
            $this->form->fields->password->hint = 'The password has to be '
                . \npdc\config::$passwordMinLength . ' characters or longer';
        }
        if (array_key_exists('notice', $_GET)) {
            switch ($_GET['notice']) {
                case 'expired':
                    $this->form->fields->action->value = 'parent/submit';
            }
        }
        if (
            array_key_exists('formid', $_POST)
            && $_POST['formid'] === $this->formId
        ) {
            $this->formController->doCheck();
            if ($this->formController->ok) {
                $person = isset($_SESSION[$this->formId]['data']['mail'])
                    ? $this->model->getUser(
                        $_SESSION[$this->formId]['data']['mail']
                    )
                    : null;
                switch ($this->formId) {
                    case 'register':
                        $this->processRegister($person);
                        break;
                    case 'register_create':
                        $this->processRegisterCreate($person);
                        break;
                }
            } 
        }
    }
    
    private function processRegister($person){
        if (!empty($person)) {
            $this->sendPasswordResetLink($person, true);
        } else {
            list($id, $code) = $this->model->requestPassword(
                ['mail'=>$_POST['mail']]
            );
            $message = "Hi \r\n\r\n"
                . 'Someone, hopefully you, requested an account for '
                . $_POST['mail'] . ' on ' . \npdc\config::$siteDomain
                . '. You can create your account using the following link:'
                . "\r\n\r\n" . $_SERVER['REQUEST_SCHEME'] . '://'
                . $_SERVER['HTTP_HOST'] . BASE_URL .  '/register/' . $id . '/'
                . $code."\r\n\r\n"
                . 'If you didn\'t request an account no action is needed. No '
                . 'account will be created. This link will expire in '
                . \npdc\config::$newExpiryHours . ' hours.'
                . "\r\n\r\nKind regards,\r\n" . \npdc\config::$siteName;
            $mail = new \npdc\lib\Mailer();
            $mail->to($_POST['mail']);
            $mail->subject('Account requested for ' . \npdc\config::$siteDomain);
            $mail->text($message);
            $mail->send();
        }
        $_SESSION['notice'] = 'A mail has been sent with a link to set your '
            . 'password. This can take a few minutes. If you do not receive a '
            . 'message soon please also check your spam folder.';
        unset($_SESSION[$this->formId]);
    }
    
    private function processRegisterCreate($person){
        if (strlen($_POST['password']) < \npdc\config::$passwordMinLength) {
            $_SESSION[$this->formId]['errors']['password'] = 
                'The new password is too short';
        } else {
            if (!empty($_POST['person'])) {
                $person_id = $_POST['person'];
                $this->model->updatePerson(
                    ['password'=>password_hash(
                        $_POST['password'],
                        PASSWORD_DEFAULT
                    )],
                    $person_id
                );
            } else {
                $person_id = $this->model->insertPerson([
                    'name'=>$_POST['name'],
                    'mail'=>$this->record['mail'],
                    'password'=>password_hash(
                        $_POST['password'],
                        PASSWORD_DEFAULT
                    )
                ]);
            }
            $this->model->usePasswordNew(\npdc\lib\Args::get('loginid'));
            $_SESSION['notice'] = 'Your account has been created';
            $account = $this->model->getById($person_id);
            $perms = $this->model->getUserLevelDetails(
                $account['user_level']
            );
            $data = [
                'name' => $account['name'],
                'mail'=>$account['mail'],
                'user_level'=>$perms['name'],
                'permissions'=>$perms['description_plain']
            ];
            $usermail = new \npdc\lib\Mailer(null, \npdc\config::$mail['contact']);
            $usermail->to($data['mail']);
            $usermail->fromTemplate('welcome', $data);
            $usermail->send();
            
            $adminmail = new \npdc\lib\Mailer();
            $adminmail->to(\npdc\config::$mail['contact']);
            $adminmail->fromTemplate('admin_new_account', $data);
            $adminmail->send();
            $_SESSION['notice'] .= '<section class="inline">Your user level is '
                . $perms['name'] . $perms['description'];
            unset($_SESSION[$this->formId]);
            header('Location: ' . BASE_URL . '/?overlay=login&referer=account');
            die();
        }
    }
}