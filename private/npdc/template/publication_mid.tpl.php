<?php

/**
 * main content of publication page
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

echo '<div id="citation"><span style="font-weight:bold">&ldquo;</span>'
    . $this->model->getCitation(
        $this->data,
        $this->data['publication_version'],
        false
    )
    . '<span style="font-weight:bold">&rdquo;</span>
    <div>Download citation as: <a href="' . BASE_URL . '/publication/'
        . $this->data['uuid'] . '.bib" style="font-variant:small-caps">BibTex</a>'
        . ' or <a href="' . BASE_URL . '/publication/' . $this->data['uuid']
        . '.ris"><abbr title="EndNote/ProCite/Reference Manager">RIS</abbr></a>'
        . '</div></div>';

?><h4>Abstract</h4>
<p><?=$this->data['abstract']?></p>

<h4>Authors</h4>
<?php

$people = $this->model->getPersons(
    $this->data['publication_id'],
    $this->data['publication_version']
);
if (count($people) === 0) {
    echo 'No persons linked to this publication yet';
} else {
    echo $this->displayTable(
        'person',
        $people,
        [
            'name' => 'Name',
            'organization_name' => 'Organization'
        ],
        ['contact', 'person_id']
    );
}
?>

<?php
if (\npdc\config::$partEnabled['dataset']) {
?>
    <h4>Datasets</h4>
    <?php
    $datasets = $this->model->getDatasets(
        $this->data['publication_id'],
        $this->data['publication_version'],
        !$this->canEdit
    );
    if (count($datasets) === 0) {
        echo 'No datasets linked to this publication yet';
    } else {
        echo $this->displayTable(
            'dataset',
            $datasets,
            [
                'title' => 'Title',
                'date_start' => 'Start date',
                'date_end' => 'End date'
            ],
            ['dataset', 'dataset_id']
        );
    }
}
?>

<?php

if (\npdc\config::$partEnabled['project']) {
?>
    <h4>Projects</h4>
    <?php
    $projects = $this->model->getProjects(
        $this->data['publication_id'],
        $this->data['publication_version'],
        $this->data['record_status'],
        !$this->canEdit
    );
    if (count($projects) === 0) {
        echo 'No projects linked to this publication yet';
    } else {
        echo $this->displayTable(
            'project',
            $projects,
            [
                'title' => 'Title',
                'nwo_project_id' => 'Funding id',
                'period' => 'Period'
            ],
            ['project', 'project_id']
        );
    }
}