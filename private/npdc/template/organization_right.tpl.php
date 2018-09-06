<?php
/**
 * Display organization details
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */
?>

<h3>Organization details</h3>
<?php
$personView = new \npdc\lib\Person();
echo $personView->showAddress($this->data);

echo '<a href="'.$this->data['website'].'">'.$this->data['website'].'</a>';

echo '<h3>People</h3>
<ul>';

$persons = $this->model->getPersons($this->data['organization_id']);
foreach($persons as $person){
	echo '<li><a href="'.BASE_URL.'/contact/'.$person['person_id'].'">'.$person['name'].'</a></li>';
}
echo '</ul>';