<?php
/**
 * Display of citation in bibtext format
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

$content_type = 'application/x-bibtex';
$fields = [
    'author' => $str,
    'title' => $citation['title'] ?? $this->data['title'],
    'year' => substr(
        $citation['release_date'] ?? $this->data['insert_timestamp'],
        0,
        4
    ),
    'url' => $url,
    'abstract' => $this->data['summary']
];
switch ($this->model->getTypeById($this->data['publication_type_id'])['bib']) {
    case 'article':
        $additional = [
            'journal' => $this->data['journal'],
            'volume' => $this->data['volume'],
            'number' => $this->data['issue'],
            'pages' => str_replace('-', '--', $this->data['pages'])
        ];
        foreach ($additional as $key=>$value){
            if (!empty($value)) {
                $fields[$key] = $value;
            }
        }
        break;
}
$output = '@' 
    . $this->model->getTypeById($this->data['publication_type_id'])['bib']
    . '{' . $id;
foreach ($fields as $key=>$value) {
    $output .= ",\n    " . $key . ' = {' . $value . '}';
}
$output .= "\n}";