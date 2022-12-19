<?php

/**
 * Email to send when user gets editor rights
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

$subject = 'Editor rights on '.\npdc\config::$siteDomain;
$text = 'Dear '.$data['name'] . ',

An Administrator has granted you edit rights on '.\npdc\config::$siteDomain.'. With these rights you are now able to:

- Add projects, data sets and publications
- Edit projects, publications and data sets for which you have been given edit rights (either by creating them or when someone else granted you those rights)

If you need to edit a project, publication or data set for which you don\'t have permission, or you have any other question, please contact the NPDC at '.\npdc\config::$mail['contact'].'.

Kind regards,
'.\npdc\config::$siteName;
 