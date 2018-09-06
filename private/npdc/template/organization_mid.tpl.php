<?php
/**
 * Display organization details
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

$datasetModel = new \npdc\model\Dataset();
$datasets = $datasetModel->getList(['organization'=>[$this->data['organization_id']]]);
echo '<h3>Datasets</h3>';
echo $this->displayTable('dataset', $datasets, ['title'=>'Title', 'date_start'=>'Start date', 'date_end'=>'End date'], ['dataset', 'dataset_id']);

$pubModel = new \npdc\model\Publication();
$publications = $pubModel->getList(['organization'=>[$this->data['organization_id']]]);
echo '<h3>Publications</h3>';
foreach ($publications as $publication) {
	echo $pubModel->getCitation($publication);
}

$projectModel = new \npdc\model\Project();
$projects = $projectModel->getList(['organization'=>[$this->data['organization_id']]]);
echo '<h3>Projects</h3>';
echo $this->displayTable('project', $projects, ['title'=>'Title', 'nwo_project_id'=>'Funding id', 'period'=>'Period'], ['project', 'project_id']);
?>