<?php

/**
 * Email to send when user gets editor rights
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

$subject = 'New account on '.\npdc\config::$siteDomain;
$text = 'Dear admin,

A new account has been created at '.\npdc\config::$siteDomain.' with the following details:

Name: '.$data['name'].'
E-mail: '.$data['email'].'

Kind regards,
'.\npdc\config::$siteName;
 