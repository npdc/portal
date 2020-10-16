<?php
/**
 * Display of citation in ris (onenote etc) format
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

$content_type = 'application/x-research-info-systems';
$output = '
TY  - DATA
ID  - '.$id.'
T1  - '.$this->data['title'].'
';
foreach($authors as $author) {
    $output .= 'AU  - '.$author.'
';
}
$output .= 'PY  - '.str_replace('-', '/', substr($citation['release_date'] ?? $this->data['insert_timestamp'],0,10)).'/
PB  - '.($citation['publisher'] ?? \npdc\config::$siteName).'
UR  - '.$url.'
N2  - '.$this->data['summary'].'
ER  -
';