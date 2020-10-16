<?php
/**
 * side bar of project
 * 
 * @package NPDC
 * @author Marten Tacoma <marten.tacoma@nioz.nl>
 */

 
if (!is_null($this->data['program_id'])) {
$progModel = new \npdc\model\Program();
?><section class="inline">
    <h4>Funding program</h4>
    <p><?=$progModel->getById($this->data['program_id'])['name'];?></p>
</section>
<?php
}
if (!is_null($this->data['nwo_project_id'])) {
?><section class="inline">
    <h4>NWO project id</h4>
    <p><?=$this->data['nwo_project_id']?></p>
</section>
<?php
}
if (!is_null($this->data['npp_theme_id'])) {
$themeModel = new \npdc\model\Npp_theme();
?><section class="inline">
    <h4>Theme</h4>
    <p><?=$themeModel->getById($this->data['npp_theme_id'])['theme_en']?></p>
</section>
<?php
}

if (!is_null($this->data['acronym'])) {
?>
<section class="inline">
    <h4>Acronym</h4>
    <p><?=$this->data['acronym']?></p>
</section>
<?php
}
?>
<?php /*
<section class="inline">
    <h4>Funding program</h4>
    <p><?=$this->data['name']?> (<?=strtok($this->data['program_start'], '-')?> - <?=strtok($this->data['program_end'], '-')?>)</p>
</section>
 */?>

<section class="inline">
    <h4>Region</h4>
    <p><?=$this->data['region']?></p>
</section>

<?php
if (!is_null($this->data['date_start'])) {
?>
<section class="inline">
    <h4>Start date</h4>
    <p><?=$this->data['date_start']?></p>
</section>
<?php
}
if (!is_null($this->data['date_start'])) {
?>
<section class="inline">
    <h4>End date</h4>
    <p><?=$this->data['date_end'] ?? 'unknown'?></p>
</section>

<?php
}
$keywords = $this->model->getKeywords($this->data['project_id'], $this->data['project_version']);
if (count($keywords) > 0) {
    echo '<h4>Keywords</h4><ul>';
    foreach($keywords as $word) {
        echo '<li>'.$word['keyword'].'</li>';
    }
    echo '</ul>';
}

$links = $this->model->getLinks($this->data['project_id'], $this->data['project_version']);
if (count($links) > 0) {
    echo '<h4>Links</h4><ul>';
    foreach($links as $link) {
        echo '<li><a href="'.checkurl($link['url']).'">'.$link['text'].'</a></li>';
    }
    echo '</ul>';
}