<?php

/**
 * Email to send when user gets editor rights
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

$subject = 'Welcome on '.\npdc\config::$siteDomain;
$text = 'Dear '.$data['name'] . ',

Your account at '.\npdc\config::$siteDomain.' has been created. You now can log in with the credentials you provided.

Your user level is '.$data['user_level'].'.
'.$data['persmissions'] .'

';

if($data['user_level'] == 'user'){
    $text .= 'If you want to be able to add or update projects, data sets or publication please send and email to '.\npdc\config::$mail['contact'].'. We will than grant you the needed rights to be able to do so.'."\n\n";
}

$text .= 'Kind regards,
'.\npdc\config::$siteName;
 