<hr/><div class="technical">
<?php if (!empty($this->data['dif_id'])) {
    echo '<span class="nobr"><strong>Dif id:</strong> <a href="https://gcmd.nasa.gov/r/d/'.$this->data['dif_id'].'">'.$this->data['dif_id'].'</a></span> | ';
}

if (!empty($this->data['doi'])) {
    echo '<span class="nobr"><strong>Doi:</strong> <a href="https://doi.org/'.$this->data['doi'].'">'.$this->data['doi'].'</a></span> | ';
}
?>
<span class="nobr"><strong>UUID:</strong> <a href="<?=BASE_URL.'/'.$this->controller->name.'/'.$this->data['uuid']?>"><?=$this->data['uuid']?></a></span> |
<span class="version-selector nobr"><strong>Version:</strong> <?php
$versions = [];
foreach ($this->versions as $version) {
    if (in_array($version['record_status'], ['published', 'archived'])) {
        $versions[] = '<a href="'.BASE_URL.'/'.$this->controller->name.'/'.$version['uuid'].'" '.($this->data[$this->controller->name.'_version'] === $version[$this->controller->name.'_version'] ? ' class="active"' : '').'>'.$version[$this->controller->name.'_version'].' ('.($version['record_status'] === 'published' ? 'current' : $version['record_status']).')</a>';
    }
}
if (count($versions) === 1) {
    echo $this->data[$this->controller->name.'_version'];
} else {
    echo ''.$this->data[$this->controller->name.'_version'].'<span>'.implode('', $versions).'</span>';
}
?></span> | <span class="nobr"><strong>Added on:</strong> <?=date('j F Y H:i', strtotime($this->data['published']))?></span></div>