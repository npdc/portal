<?php

include 'dataset/citation.php';
?>
<button onclick="$('.hiddenSubDiv').slideDown();$('.collapsible').removeClass('hidden');">Expand all</button> <button onclick="$('.hiddenSubDiv').slideUp();$('.collapsible').addClass('hidden');">Collapse all</button>
<?php
if(!empty($this->data['dif_id'])){
?>
<section class="inline">
	<h4>Dif id</h4>
	<p><?=$this->data['dif_id']?></p>
</section>
<?php
}

$parts = [
	['Summary', 'field', 'summary', 'general'],
	['Purpose', 'field', 'purpose', 'general'],
	['Temporal coverage', 'file', 'time', 'coverage#temporal'],
	['Platform &amp; Instruments', 'file', 'platform', 'methods'],
	['Data resolution', 'file', 'resolution', 'coverage#resolution'],
	['Involved people and organizations', 'file', 'people', 'people'],
	['Dataset progress and usage', 'file', 'usage', 'usage'],
	['Projects, publications and other links', 'file', 'references', 'references']
];

foreach($parts as $part){
	$content = '';
	if($part[1] === 'field' && !empty($this->data[$part[2]])){
		$content = $this->data[$part[2]];
	} elseif($part[1] === 'file'){
		ob_start();
		include 'dataset/'.$part[2].'.php';
		$content = ob_get_clean();
	}
	if(!empty($content) || $this->canEdit){
		echo '<h3 class="collapsible hidden">'.$part[0].($this->data['record_status'] === 'draft' && $this->canEdit ? ' [<a href="'.BASE_URL.'/dataset/'.$this->data['dataset_id'].'/edit/'.$part[3].'">edit</a>]' : '').'</h3><div style="display:none" class="hiddenSubDiv">'
			. $content
			. '</div><hr/>';
	}
}