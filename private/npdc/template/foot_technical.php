<hr><div class="technical">
<?php if(!empty($this->data['dif_id'])){
	echo '<nobr><strong>Dif id:</strong> <a href="https://gcmd.nasa.gov/r/d/[GCMD]'.$this->data['dif_id'].'">'.$this->data['dif_id'].'</a></nobr> | ';
}

if(!empty($this->data['doi'])){
	echo '<nobr><strong>Doi:</strong> <a href="https://doi.org/'.$this->data['doi'].'">'.$this->data['doi'].'</a></nobr> | ';
}
?>
<nobr><strong>UUID:</strong> <a href="<?=BASE_URL.'/'.$this->controller->name.'/'.$this->data['uuid']?>"><?=$this->data['uuid']?></a></nobr> |
<nobr><span class="version-selector"><strong>Version:</strong> <?php
$versions = [];
foreach($this->versions as $version){
	if(in_array($version['record_status'], ['published', 'archived'])){
		$versions[] = '<a href="'.BASE_URL.'/'.$this->controller->name.'/'.$version['uuid'].'">'.$version[$this->controller->name.'_version'].' ('.($version['record_status'] === 'published' ? 'current' : $version['record_status']).')</a>';
	}
}
if(count($versions) === 1){
	echo $this->data[$this->controller->name.'_version'];
} else {
	echo ''.$this->data[$this->controller->name.'_version'].'<div>'.implode('', $versions).'</div>';
}
?></span></nobr> | <nobr><strong>Added on:</strong> <?=date('j F Y H:i', strtotime($this->data['published']))?></nobr></div>