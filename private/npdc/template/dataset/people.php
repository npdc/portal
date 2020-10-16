<?php
/**
 * Display if people and organizations involved in data collection/processing
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */
?>

<section>
    <h4>Originating center</h4>
    <p>
    <?php
    if (empty($this->data['originating_center'])) {
        echo 'Please provide in \'Involved people\'';
    } else {
        $orgModel = new \npdc\model\Organization();
        echo $orgModel->getById($this->data['originating_center'])['organization_name'];
    }
    ?>
    </p>
</section><h4>Participants</h4><?php

$people = $this->model->getPersons($this->data['dataset_id'], $this->data['dataset_version']);
if (count($people) === 0) {
    echo 'No persons linked to this dataset yet';
} else {
    echo $this->displayTable('person', $people, ['name'=>'Name', 'organization_name'=>'Organization', 'role'=>['Role', 'array']], ['contact', 'person_id']);
}