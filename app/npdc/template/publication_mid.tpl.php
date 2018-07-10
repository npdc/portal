<?php

/**
 * main content of publication page
 */

?><h4>Abstract</h4>
<p><?=$this->data['abstract']?></p>

<h4>Authors</h4>
<?php

$people = $this->model->getPersons($this->data['publication_id'], $this->data['publication_version']);
if(count($people) === 0){
	echo 'No persons linked to this publication';
} else {
	echo $this->displayTable('person', $people, ['name'=>'Name', 'organization_name'=>'Organization'], ['contact', 'person_id']);
}
?>

<?php
if(\npdc\config::$partEnabled['dataset']){
?>
	<h4>Datasets</h4>
	<?php
	$datasets = $this->model->getDatasets($this->data['publication_id'], $this->data['publication_version'], !$this->canEdit);
	if(count($datasets) === 0){
		echo 'No datasets linked to this publication';
	} else {
		echo $this->displayTable('dataset', $datasets, ['title'=>'Title', 'date_start'=>'Start date', 'date_end'=>'End date'], ['dataset', 'dataset_id']);
	}
}
?>

<?php

if(\npdc\config::$partEnabled['project']){
?>
	<h4>Projects</h4>
	<?php
	$projects = $this->model->getProjects($this->data['publication_id'], $this->data['publication_version'], $this->data['record_status'], !$this->canEdit);
	if(count($projects) === 0){
		echo 'No projects linked to this publication';
	} else {
		echo $this->displayTable('project', $projects, ['nwo_project_id'=>'Project ID', 'title'=>'Title', 'period'=>'Period'], ['project', 'project_id']);
	}
}
?>
<hr/>
<div class="technical"><nobr><strong>UUID:</strong> <?=$this->data['uuid']?></nobr> | <nobr><strong>Version:</strong> <?=$this->data['publication_version']?></nobr></div>