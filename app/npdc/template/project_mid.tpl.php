<?php
/**
 * main content of project page
 */

?><h4>Summary</h4>
<p><?=$this->data['summary']?></p>

<h4>People involved</h4>
<?php

$people = $this->model->getPersons($this->data['project_id'], $this->data['project_version']);
if(count($people) === 0){
	echo 'No persons linked to this project';
} else {
	echo $this->displayTable('person', $people, ['name'=>'Name', 'organization_name'=>'Organization', 'role'=>'Role'], ['contact', 'person_id']);
}

$parents = $this->model->getParents($this->data['project_id']);
if(count($parents) > 0){
	?>
	<h4>Main project<?=count($parents)>1 ? 's' : '' ?></h4>
	<?php
	echo $this->displayTable('project', $parents, ['nwo_project_id'=>'ID', 'title'=>'Title', 'period'=>'Period'], ['project', 'project_id']);
}

$children = $this->model->getChildren($this->data['project_id']);
if(count($children) > 0){
	?>
	<h4>Daughter project<?=count($children)>1 ? 's' : '' ?></h4>
	<?php
	echo $this->displayTable('project', $children, ['nwo_project_id'=>'ID', 'title'=>'Title', 'period'=>'Period'], ['project', 'project_id']);
}

if(\npdc\config::$partEnabled['dataset']){
?>
	<h4>Datasets</h4>
	<?php
	$datasets = $this->model->getDatasets($this->data['project_id'], $this->data['project_version'], !$this->canEdit);
	if(count($datasets) === 0){
		echo 'No datasets linked to this project';
	} else {
		echo $this->displayTable('dataset', $datasets, ['title'=>'Title', 'date_start'=>'Start date', 'date_end'=>'End date'], ['dataset', 'dataset_id']);
	}
}

if(\npdc\config::$partEnabled['publication'] || $this->session->userLevel >= NPDC_ADMIN){
?>
	<h4>Publications</h4>
	<?php
	$publications = $this->model->getPublications($this->data['project_id'], $this->data['project_version'], !$this->canEdit);
	if(count($publications) === 0){
		echo 'No publications linked to this project';
	} else {
		$list2 = [];
		$pubModel = new \npdc\model\Publication();
		foreach($publications as $item){
			$item['authors'] = $pubModel->getAuthors($item['publication_id'], $item['publication_version']);
			$list2[] = $item;
		}
		echo $this->displayTable('publication', $list2, ['authors'=>'Authors', 'title'=>'Title', 'year'=>'year'], ['publication', 'publication_id']);
	}
}
?>
<hr/><div class="technical"><nobr><strong>UUID:</strong> <?=$this->data['uuid']?></nobr> | <nobr><strong>Version:</strong> <?=$this->data['project_version']?></nobr></div>