<?php

/**
 * Publication list for person report
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

$publications = $this->model->getPublications($this->data['person_id'], false);
echo '<h3 class="collapsible">Publications</h3><div class="hiddenSubDiv">';
if (count($publications) === 0) {
    echo '<p>'.$this->data['name'].' is not listed in any publications</p>';
} else {
    foreach($publications as $publication) {
        echo '<h4 class="collapsible">'.$publication['title']
        . ($publication['record_status'] === 'draft' && $publication['publication_version'] === 1 ? ' <i>(unpublished)</i>' : '')
        . '</h4><div class="hiddenSubDiv">
        <p>'.$publicationModel->getAuthors($publication['publication_id'], $publication['publication_version'])
        . ' ('.$publication['year'].')
        | '.$publication['journal'].'
        <span class="screenonly"> | <a href="'.BASE_URL.'/publication/'.$publication['publication_id'].'">More details</a></span>
        </p>';
        $projects = $publicationModel->getProjects($publication['publication_id'], $publication['publication_version'], false);
        if (count($projects) > 0) {
            echo '<h5>Projects</h5>';
            foreach($projects as $project) {
                echo '<p><a href="'.BASE_URL.'/project/'.$project['project_id'].'">'.$project['title'].'</a>'
                . ($project['record_status'] === 'draft' && project['project_version'] === 1 ? ' <i>(unpublished)</i>' : '')
                . '|&nbsp;'.$project['date_start'].' - '.$project['date_end']
                . (empty($project['nwo_project_id']) ? '' : ' |&nbsp;'.$project['nwo_project_id'])
                . '</p>';
                $i++;
            }
        }

        $datasets = $publicationModel->getDatasets($publication['publication_id'], $publication['publication_version'], false);
        if (count($datasets) > 0) {
            echo '<h5>Datasets</h5>';
            foreach($datasets as $dataset) {
                echo '<p><a href="'.BASE_URL.'/dataset/'.$dataset['dataset_id'].'">'.$dataset['title'].'</a>'
                . ($dataset['record_status'] === 'draft' && $dataset['dataset_version'] === 1 ? ' <i>(unpublished)</i>' : '')
                . ' |&nbsp;'.$dataset['date_start'].' - '.$dataset['date_end'].' |&nbsp;'.$dataset['dif_id'].'</p>';
            }
        }
        if (count($datasets) === 0 && count($projects) === 0) {
            echo 'No projects or datasets linked</p>';
        } elseif (count($datasets) === 0) {
            echo 'No datasets linked</p>';
        } elseif (count($projects) === 0) {
            echo 'No projects linked</p>';
        }
        echo '</div><hr/>';
    }
}
echo '</div><hr/>';