<?php
/**
 * Links to other content types and external source related to a dataset
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

echo '<h4>Projects</h4>';
$projects = $this->model->getProjects($this->data['dataset_id'], $this->data['dataset_version'], !$this->canEdit);
if (count($projects) === 0) {
    echo 'No projects linked to this dataset yet';
} else {
    echo $this->displayTable('project', $projects, ['title'=>'Title', 'nwo_project_id'=>'Funding id', 'period'=>'Period'], ['project', 'project_id']);
}

echo '<h4>Publications</h4>';
$publications = $this->model->getPublications($this->data['dataset_id'], $this->data['dataset_version'], !$this->canEdit);
if (count($publications) === 0) {
    echo 'No publications linked to this dataset yet';
} else {
    $publicationModel = new \npdc\model\Publication();
    foreach ($publications as $publication) {
        echo $publicationModel->getCitation($publication);
    }
}

echo '<h4>Links</h4>';
$links = $this->model->getLinks($this->data['dataset_id'], $this->data['dataset_version']);
if (count($links) === 0) {
    echo 'No links';
} else {
    foreach ($links as $link) {
        $urls = $this->model->getLinkUrls($link['dataset_link_id'], $this->data['dataset_version']);
        echo '<p'
            .(count($urls) === 1 
            ? ' style="margin-bottom:0px"><a href="'.checkurl($urls[0]['url']).'">'.$link['title'].'</a>' 
            : '>'.$link['title'])
            .'<br/>'.$link['description'].'</p>';
        if (count($urls) > 1) {
            echo '<ul style="margin-top: 0px;">';
            foreach ($urls as $url) {
                echo '<li><a href="'.checkurl($url['url']).'">'.checkurl($url['url']).'</a></li>';
            }
            echo '</ul>';
        }
    }
}

$citations = $this->model->getCitations($this->data['dataset_id'], $this->data['dataset_version'], 'other');
if (count($citations) > 0) {
    echo '<h4>Other references</h4>';
    foreach ($citations as $citation) {
        echo '<p>'
            . $citation['creator']
            . ' ('.substr($citation['release_date'],0,4).').'
            . ' <em>'.($citation['title'] ?? $this->data['title']).'.</em>'
            . (!is_null($citation['version']) ? ' ('.$citation['version'].')' : '')
            . (!is_null($citation['release_place']) ? ' '.$citation['release_place'].'.' : '')
            . (!is_null($citation['editor']) ? ' Edited by '.$citation['editor'].'.' : '')
            . (!is_null($citation['publisher']) ? ' Published by '.$citation['publisher'].'.' : '')
            . '</p>';
    }
}

$related = $this->model->getRelatedDatasets($this->data['dataset_id'], $this->data['dataset_version']);
if (count($related) > 0) {
    echo '<h4>Related datasets</h4><ul>';
    foreach ($related as $set) {
        if (!empty($set['doi'])) {
            $l = '<a href="https://dx.doi.org/'.$set['doi'].'">https://dx.doi.org/'.$set['doi'].'</a>';
        } elseif (!empty($set['internal_related_dataset_id'])) {
            $ds = $this->model->getById($set['internal_related_dataset_id']);
            if (empty($ds) && $this->canEdit) {
                $ds = $this->model->getById($set['internal_related_dataset_id'], 'draft');
            }
            if (empty($ds)) {
                continue;
            }
            $l = '<a href="'.BASE_URL.'/'.$ds['uuid'].'">'.$ds['title'].'</a>';
        } else {
            $l = '<a href="'.$set['url'].'">'.$set['url'].'</a>';
        }
        echo '<li>'.$l.' - <em>'
            . ($set['same'] ? 'The same dataset' : 'A related dataset')
            . (empty($set['relation']) ? '' : '<span style="font-size:80%"><br/>Relation: '.$set['relation'].'</a>')
            . '</em></li>';
    }
    echo '</ul>';
}
