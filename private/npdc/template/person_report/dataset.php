<?php

/**
 * Dataset list for person report
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

$datasets = $this->model->getDatasets($this->data['person_id'], false);
echo '<h3 class="collapsible">Datasets</h3><div class="hiddenSubDiv">';
if (count($datasets) === 0) {
    echo '<p>'.$this->data['name'].' is not listed in any datasets</p>';
} else {
    foreach($datasets as $dataset) {
        echo '<h4 class="collapsible">'.$dataset['title']
        . ($dataset['record_status'] === 'draft' && $dataset['dataset_version'] === 1 ? ' <i>(unpublished)</i>' : '')
        . '</h4>
        <div class="hiddenSubDiv">
        <p>'.$dataset['date_start'].' - '.$dataset['date_end']
        .' |&nbsp;'.$dataset['dif_id'];
        $people = $datasetModel->getPersons($dataset['dataset_id'], $dataset['dataset_version']);
        if (count($people) > 1) {
            echo ' |&nbsp;With: ';
            $i = 0;
            foreach($people as $person) {
                if ($person['person_id'] !== $this->data['person_id']) {
                    echo ($i>0 ? ', ' : '').'<a href="'.BASE_URL.'/person/'.$person['person_id'].'/report">'.$person['name'].'</a>';
                    $i++;
                }
            }
        }
        echo '<span class="screenonly"> |&nbsp;<a href="'.BASE_URL.'/dataset/'.$dataset['dataset_id'].'">More details</a></span></p>';
        $projects = $datasetModel->getProjects($dataset['dataset_id'], $dataset['dataset_version'], false);
        if (count($projects) > 0) {
            echo '<h5>Projects</h5>';
            foreach($projects as $project) {
                echo '<p><a href="'.BASE_URL.'/project/'.$project['project_id'].'">'.$project['title'].'</a>'
                . ($project['record_status'] === 'draft' && $project['project_version'] === 1 ? ' <i>(unpublished)</i>' : '')
                . ' |&nbsp;'.$project['date_start'].' - '.$project['date_end']
                . (empty($project['nwo_project_id']) ? '' : ' |&nbsp;'.$project['nwo_project_id'])
                . '</p>';
                $i++;
            }
        }

        $publications = $datasetModel->getpublications($dataset['dataset_id'], $dataset['dataset_version'], false);
        if (count($publications) > 0) {
            echo '<h5>Publications</h5>';
            foreach($publications as $publication) {
                echo '<p><a href="'.BASE_URL.'/publication/'.$publication['publication_id'].'">'.$publicationModel->getAuthors($publication['publication_id'], $publication['publication_version']).' ('.$publication['year'].'), '.$publication['title'].'</a>'
                . ($publication['record_status'] === 'draft' && $publication['publication_version'] === 1 ? ' <i>(unpublished)</i>' : '')
                . '</p>';
            }
        }
        
        
        if (count($projects) === 0 && count($publications) === 0) {
            echo 'No projects or publications linked</p>';
        } elseif (count($projects) === 0) {
            echo 'No projects linked</p>';
        } elseif (count($publications) === 0) {
            echo 'No publications linked</p>';
        }
    
        echo '</div><hr/>';
    }
}
echo '</div><hr/>';