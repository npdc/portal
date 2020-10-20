<?php
/**
 * Display of citation in ris (onenote etc) format
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

$content_type = 'application/x-research-info-systems';
$output = '
TY  - '.$this->model->getTypeById($this->data['publication_type_id'])['ris'].'
ID  - '.$id.'
T1  - '.$this->data['title'].'
';
foreach ($authors as $author) {
    $output .= 'AU  - '.$author.'
';
}
switch ($this->model->getTypeById($this->data['publication_type_id'])['ris']) {
    case 'JOUR':
        list($start, $end) = explode('-', $this->data['pages']);
        $output .= 'JO  - '.$this->data['journal'].'
VL  - '.$this->data['volume'].'
IS  - '.$this->data['issue'].'
SP  - '.$start.'
EP  - '.$end.'
';
        break;
    case 'CHAP':
    $output .= 'JO  - '.$this->data['journal'].'
';
        break;
}
$output .= 'PY  - '.str_replace('-', '/', substr($citation['release_date'] ?? $this->data['insert_timestamp'],0,10)).'/
UR  - '.$url.'
N2  - '.$this->data['abstract'].'
ER  -
';