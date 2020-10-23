<?php

/**
 * side bar of publication
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

if (!empty($this->data['url'])) {
    echo '<h4>External resource</h4><p><a href="' . checkUrl($this->data['url'])
        . '">Link</a></p>';
}

$fields = [
    'publication_type_id' => 'Publication type'
    , 'date' => 'Date'
    , 'journal' => 'Journal'
    , 'volume' => 'Volume'
    , 'issue' => 'Issue'
    , 'pages' => 'Pages'
    , 'isbn' => 'ISBN'
    , 'doi' => 'DOI'
];

foreach ($fields as $id => $label) {
    if (!empty($this->data[$id])) {
        echo '<section class="inline"><h4>' . $label . '</h4><p>';
        switch ($id) {
            case 'doi':
                $doi = substr($this->data[$id], strpos($this->data[$id], '10.'));
                echo '<a href="https://doi.org/' . $doi . '">' . $doi . '</a></p>';
                break;
            case 'date':
                echo str_replace('-00', '', $this->data[$id]);
                break;
            case 'publication_type_id':
                echo $this->model->getTypeById($this->data[$id])['label'];
                break;
            default:
                echo $this->data[$id];
        }
        echo '</section>';
    }
}


$keywords = $this->model->getKeywords(
    $this->data['publication_id'],
    $this->data['publication_version']
);
if (count($keywords) > 0) {
    echo '<h4>Keywords</h4><ul>';
    foreach ($keywords as $word) {
        echo '<li>' . $word['keyword'] . '</li>';
    }
    echo '</ul>';
}
