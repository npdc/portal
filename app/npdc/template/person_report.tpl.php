<p><?=$this->data['organization_name']?> - <?=$this->data['organization_city']?><br/>

<?php
$projectModel = new \npdc\model\Project();
$publicationModel = new \npdc\model\Publication();
$datasetModel = new \npdc\model\Dataset();

$perms = $this->model->getUserLevelDetails($this->data['user_level']);
echo $this->data['mail'];
$fields = ['phone_personal'=>'Direct','phone_secretariat'=>'General','phone_mobile'=>'Mobile'];
foreach($fields as $id=>$label){
	if(!empty($this->data[$id])){
		echo ' |&nbsp;'.$this->data[$id].' ('.$label.')';
	}
}
echo ' |&nbsp;'.$perms['name'].'</p>';

?>
<button onclick="$('.hiddenSubDiv').slideDown();$('.collapsible').removeClass('hidden');">Expand all</button> <button onclick="$('.hiddenSubDiv').slideUp();$('.collapsible').addClass('hidden');">Collapse all</button>

<?php
require 'person_report/project.php';
require 'person_report/dataset.php';
require 'person_report/publication.php';