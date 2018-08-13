<?php

include 'dataset/citation.php';

$parts = [
	['Summary', 'field', 'summary', 'general'],
	['Purpose', 'field', 'purpose', 'general'],
	['', 'file', 'time', 'coverage#temporal'],
	['', 'file', 'platform', 'methods'],
	['', 'file', 'resolution', 'coverage#resolution'],
	['', 'file', 'people', 'people'],
	['', 'file', 'usage', 'usage'],
	['', 'file', 'references', 'references']
/*	['Temporal coverage', 'file', 'time', 'coverage#temporal'],
	['Platform &amp; Instruments', 'file', 'platform', 'methods'],
	['Data resolution', 'file', 'resolution', 'coverage#resolution'],
	['Involved people and organizations', 'file', 'people', 'people'],
	['Dataset progress and usage', 'file', 'usage', 'usage'],
	['Projects, publications and other links', 'file', 'references', 'references']*/
];

foreach($parts as $part){
	$content = '';
	if($part[1] === 'field' && !empty($this->data[$part[2]])){
		$content = '<div class="overflow">'.(strpos('<',$this->data[$part[2]]) ? $this->data[$part[2]] : nl2br($this->data[$part[2]])).'</div>';
	} elseif($part[1] === 'file'){
		ob_start();
		include 'dataset/'.$part[2].'.php';
		$content = ob_get_clean();
	}
	if(!empty($content) || ($this->data['record_status'] === 'draft' && $this->canEdit)){
		echo '<div><h4>'.$part[0].($this->data['record_status'] === 'draft' && $this->canEdit ? ' [<a href="'.BASE_URL.'/dataset/'.$this->data['dataset_id'].'/edit/'.$part[3].'">edit</a>]' : '').'</h4>'
		//echo '<h3 class="collapsible hidden">'.$part[0].($this->data['record_status'] === 'draft' && $this->canEdit ? ' [<a href="'.BASE_URL.'/dataset/'.$this->data['dataset_id'].'/edit/'.$part[3].'">edit</a>]' : '').'</h3><div style="display:none" class="hiddenSubDiv">'
			. $content
			. '</div><hr/>';
	}
}
echo '<div class="technical">';
if(!empty($this->data['dif_id'])){
	echo '<strong>Dif id:</strong> '.$this->data['dif_id'].' | ';
}
?>
<nobr><strong>UUID:</strong> <a href="<?=BASE_URL.'/'.$this->data['uuid']?>"><?=$this->data['uuid']?></a></nobr> | <nobr><strong>Version:</strong> <?=$this->data['dataset_version']?></nobr></div>