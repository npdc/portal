<?php

/**
 * Project list for person report
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

$projects = $this->model->getProjects($this->data['person_id'], false);
echo '<h3 class="collapsible">Projects</h3><div class="hiddenSubDiv">';
if(count($projects) === 0){
	echo '<p>'.$this->data['name'].' is not listed in any project</p>';
} else {
	foreach($projects as $project){
		echo '<h4 class="collapsible">'.$project['title']
		. ($project['record_status'] === 'draft' && $project['project_version'] === 1 ? ' <i>(unpublished)</i>' : '')
		. '</h4>
		<div class="hiddenSubDiv">
		<p>'.$project['date_start'].' - '.$project['date_end']
		. (empty($project['nwo_project_id']) ? '' : ' |&nbsp;'.$project['nwo_project_id']);
		$people = $projectModel->getPersons($project['project_id'], $project['project_version']);
		if(count($people) > 1){
			echo ' |&nbsp;With: ';
			$i = 0;
			foreach($people as $person){
				if($person['person_id'] !== $this->data['person_id']){
					echo ($i>0 ? ', ' : '').'<a href="'.BASE_URL.'/person/'.$person['person_id'].'/report">'.$person['name'].'</a>';
					$i++;
				}
			}
		}
		echo '<span class="screenonly"> |&nbsp;<a href="'.BASE_URL.'/project/'.$project['project_id'].'">More details</a></span></p>';
		$datasets = $projectModel->getDatasets($project['project_id'], $project['project_version'], false);
		if(count($datasets) > 0){
			echo '<h5>Datasets</h5>';
			foreach($datasets as $dataset){
				echo '<p><a href="'.BASE_URL.'/dataset/'.$dataset['dataset_id'].'">'.$dataset['title'].'</a>'
				. ($dataset['record_status'] === 'draft' && $dataset['dataset_version'] === 1 ? ' <i>(unpublished)</i>' : '')
				. ' |&nbsp;'.$dataset['date_start'].' - '.$dataset['date_end'].' |&nbsp;'.$dataset['dif_id'].'</p>';
			}
		}
		
		$publications = $projectModel->getpublications($project['project_id'], $project['project_version'], false);
		if(count($publications) > 0){
			echo '<h5>Publications</h5>';
			foreach($publications as $publication){
				echo '<p><a href="'.BASE_URL.'/publication/'.$publication['publication_id'].'">'.$publicationModel->getAuthors($publication['publication_id'], $publication['publication_version']).' ('.$publication['year'].'), '.$publication['title'].'</a>'
				. ($publication['record_status'] === 'draft' && $publication['publication_version'] === 1 ? ' <i>(unpublished)</i>' : '')
				. '</p>';
			}
		}
		
		if(count($datasets) === 0 && count($publications) === 0){
			echo 'No datasets or publications linked</p>';
		} elseif(count($datasets) === 0){
			echo 'No datasets linked</p>';
		} elseif(count($publications) === 0){
			echo 'No publications linked</p>';
		}
		
		echo '</div><hr/>';
	}
}
echo '</div><hr/>';